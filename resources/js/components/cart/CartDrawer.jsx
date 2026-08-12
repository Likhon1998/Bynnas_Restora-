import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Minus, Plus, ShoppingBag, Trash2, X } from 'lucide-react';
import { useCart } from '../../context/CartContext';
import { formatMoney, getSiteSettings } from '../../data/siteSettings';

export default function CartDrawer() {
    const {
        items,
        open,
        setOpen,
        changeQty,
        removeItem,
        clearCart,
        subtotal,
        service,
        tax,
        total,
        cartCount,
        orderingEnabled,
        vatRate,
        serviceRate,
    } = useCart();
    const settings = getSiteSettings();
    const [checkoutOpen, setCheckoutOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(null);
    const [form, setForm] = useState({
        customer_name: '',
        customer_phone: '',
        type: 'takeaway',
        notes: '',
    });

    if (!open) return null;

    const close = () => {
        setOpen(false);
        setCheckoutOpen(false);
        setError('');
    };

    const submitOrder = async (e) => {
        e.preventDefault();
        setError('');
        setSubmitting(true);
        try {
            const res = await fetch('/api/web/orders', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    ...form,
                    items: items.map((item) => ({
                        menu_item_id: item.id,
                        quantity: item.qty,
                    })),
                }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Could not place order.');
            }
            setSuccess(data);
            clearCart();
            setCheckoutOpen(false);
            setForm({ customer_name: '', customer_phone: '', type: 'takeaway', notes: '' });
        } catch (err) {
            setError(err.message || 'Checkout failed.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-[80]">
            <button
                type="button"
                aria-label="Close cart"
                className="absolute inset-0 bg-black/50"
                onClick={close}
            />
            <aside className="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl">
                <div className="flex items-center justify-between border-b border-line px-5 py-4">
                    <div>
                        <h2 className="font-display text-2xl font-semibold text-ink">Your Cart</h2>
                        <p className="text-xs text-muted">{cartCount} item(s)</p>
                    </div>
                    <button
                        type="button"
                        onClick={close}
                        className="rounded-full p-2 text-ink hover:bg-cream"
                        aria-label="Close"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <div className="flex-1 overflow-y-auto px-5 py-4">
                    {success ? (
                        <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                            <p className="font-semibold">Order placed!</p>
                            <p className="mt-1">
                                Order <strong>{success.order_number}</strong> · Total{' '}
                                {formatMoney(success.total, settings)}
                            </p>
                            <p className="mt-2 text-emerald-800/80">
                                We’ll confirm shortly. You can track updates with the restaurant.
                            </p>
                            <button
                                type="button"
                                className="mt-3 text-sm font-semibold text-ember underline"
                                onClick={() => setSuccess(null)}
                            >
                                Continue shopping
                            </button>
                        </div>
                    ) : null}

                    {!success && items.length === 0 ? (
                        <div className="flex h-full flex-col items-center justify-center gap-3 text-center text-muted">
                            <ShoppingBag className="h-10 w-10 opacity-40" />
                            <p className="text-sm">Your cart is empty.</p>
                            <Link
                                to="/menu"
                                onClick={close}
                                className="text-sm font-semibold text-ember hover:underline"
                            >
                                Browse the menu
                            </Link>
                        </div>
                    ) : null}

                    {!success &&
                        items.map((item) => (
                            <div key={item.id} className="mb-4 flex gap-3 border-b border-line pb-4">
                                <img
                                    src={item.image}
                                    alt={item.name}
                                    className="h-16 w-16 rounded-lg object-cover"
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-semibold text-ink">{item.name}</p>
                                    <p className="text-sm text-ember">
                                        {formatMoney(item.price, settings)}
                                    </p>
                                    <div className="mt-2 flex items-center gap-2">
                                        <div className="inline-flex items-center gap-1 rounded-md border border-line px-1.5 py-1">
                                            <button
                                                type="button"
                                                aria-label="Decrease"
                                                onClick={() => changeQty(item.id, -1)}
                                                className="rounded p-1 hover:bg-cream"
                                            >
                                                <Minus className="h-3.5 w-3.5" />
                                            </button>
                                            <span className="w-5 text-center text-sm font-semibold">
                                                {item.qty}
                                            </span>
                                            <button
                                                type="button"
                                                aria-label="Increase"
                                                onClick={() => changeQty(item.id, 1)}
                                                className="rounded p-1 hover:bg-cream"
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => removeItem(item.id)}
                                            className="rounded p-1.5 text-muted hover:bg-cream hover:text-red-600"
                                            aria-label={`Remove ${item.name}`}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                                <p className="text-sm font-semibold text-ink">
                                    {formatMoney(item.price * item.qty, settings)}
                                </p>
                            </div>
                        ))}

                    {!success && checkoutOpen && items.length > 0 ? (
                        <form id="web-checkout-form" onSubmit={submitOrder} className="mt-2 space-y-3">
                            <h3 className="font-display text-lg font-semibold text-ink">Checkout</h3>
                            <label className="block text-xs font-semibold text-muted">
                                Your name
                                <input
                                    required
                                    className="field mt-1"
                                    value={form.customer_name}
                                    onChange={(e) =>
                                        setForm((f) => ({ ...f, customer_name: e.target.value }))
                                    }
                                />
                            </label>
                            <label className="block text-xs font-semibold text-muted">
                                Phone
                                <input
                                    required
                                    className="field mt-1"
                                    value={form.customer_phone}
                                    onChange={(e) =>
                                        setForm((f) => ({ ...f, customer_phone: e.target.value }))
                                    }
                                />
                            </label>
                            <label className="block text-xs font-semibold text-muted">
                                Order type
                                <select
                                    className="field mt-1"
                                    value={form.type}
                                    onChange={(e) =>
                                        setForm((f) => ({ ...f, type: e.target.value }))
                                    }
                                >
                                    <option value="takeaway">Takeaway</option>
                                    <option value="delivery">Delivery</option>
                                    <option value="dinein">Dine-in</option>
                                </select>
                            </label>
                            <label className="block text-xs font-semibold text-muted">
                                Notes
                                <textarea
                                    className="field mt-1"
                                    rows={2}
                                    style={{ height: 'auto', padding: '10px 12px' }}
                                    value={form.notes}
                                    onChange={(e) =>
                                        setForm((f) => ({ ...f, notes: e.target.value }))
                                    }
                                />
                            </label>
                            {error ? <p className="text-sm text-red-600">{error}</p> : null}
                        </form>
                    ) : null}
                </div>

                {!success && items.length > 0 ? (
                    <div className="border-t border-line px-5 py-4">
                        <div className="space-y-1.5 text-sm">
                            <div className="flex justify-between text-muted">
                                <span>Subtotal</span>
                                <span>{formatMoney(subtotal, settings)}</span>
                            </div>
                            <div className="flex justify-between text-muted">
                                <span>Service {serviceRate}%</span>
                                <span>{formatMoney(service, settings)}</span>
                            </div>
                            <div className="flex justify-between text-muted">
                                <span>Tax {vatRate}%</span>
                                <span>{formatMoney(tax, settings)}</span>
                            </div>
                            <div className="flex justify-between text-base font-semibold text-ink">
                                <span>Total</span>
                                <span>{formatMoney(total, settings)}</span>
                            </div>
                        </div>

                        {!orderingEnabled ? (
                            <p className="mt-3 text-sm text-amber-700">
                                Online ordering is disabled in settings.
                            </p>
                        ) : null}

                        <div className="mt-4 grid gap-2">
                            {!checkoutOpen ? (
                                <button
                                    type="button"
                                    disabled={!orderingEnabled}
                                    onClick={() => setCheckoutOpen(true)}
                                    className="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Checkout
                                </button>
                            ) : (
                                <button
                                    type="submit"
                                    form="web-checkout-form"
                                    disabled={submitting || !orderingEnabled}
                                    className="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {submitting ? 'Placing order…' : 'Place order'}
                                </button>
                            )}
                            <button
                                type="button"
                                onClick={clearCart}
                                className="text-sm font-medium text-muted hover:text-ink"
                            >
                                Clear cart
                            </button>
                        </div>
                    </div>
                ) : null}
            </aside>
        </div>
    );
}
