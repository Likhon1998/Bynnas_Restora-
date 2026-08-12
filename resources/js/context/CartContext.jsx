import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import { getSiteSettings } from '../data/siteSettings';

const STORAGE_KEY = 'bynnas_web_cart_v1';
const CartContext = createContext(null);

function loadCart() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

export function CartProvider({ children }) {
    const [items, setItems] = useState(() =>
        typeof window !== 'undefined' ? loadCart() : [],
    );
    const [open, setOpen] = useState(false);
    const [toast, setToast] = useState(null);

    useEffect(() => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch {
            /* ignore quota errors */
        }
    }, [items]);

    useEffect(() => {
        if (!toast) return undefined;
        const t = window.setTimeout(() => setToast(null), 2200);
        return () => window.clearTimeout(t);
    }, [toast]);

    const settings = getSiteSettings();
    const orderingEnabled = settings.online_ordering_enabled !== false;
    const vatRate = Number(settings.vat_rate ?? 7);
    const serviceRate = Number(settings.service_charge_rate ?? 5);

    const addItem = useCallback(
        (item, qty = 1) => {
            if (!orderingEnabled) {
                setToast('Online ordering is currently disabled.');
                return;
            }
            if (!item?.id) return;

            setItems((prev) => {
                const found = prev.find((p) => String(p.id) === String(item.id));
                if (found) {
                    return prev.map((p) =>
                        String(p.id) === String(item.id)
                            ? { ...p, qty: p.qty + qty }
                            : p,
                    );
                }
                return [
                    ...prev,
                    {
                        id: item.id,
                        name: item.name,
                        price: Number(item.price) || 0,
                        image: item.image || item.image_url || '',
                        qty,
                    },
                ];
            });
            setToast(`${item.name} added to cart`);
            setOpen(true);
        },
        [orderingEnabled],
    );

    const removeItem = useCallback((id) => {
        setItems((prev) => prev.filter((p) => String(p.id) !== String(id)));
    }, []);

    const changeQty = useCallback((id, delta) => {
        setItems((prev) =>
            prev
                .map((p) =>
                    String(p.id) === String(id)
                        ? { ...p, qty: Math.max(0, p.qty + delta) }
                        : p,
                )
                .filter((p) => p.qty > 0),
        );
    }, []);

    const setQty = useCallback((id, qty) => {
        const next = Math.max(0, Number(qty) || 0);
        setItems((prev) =>
            prev
                .map((p) => (String(p.id) === String(id) ? { ...p, qty: next } : p))
                .filter((p) => p.qty > 0),
        );
    }, []);

    const clearCart = useCallback(() => setItems([]), []);

    const subtotal = useMemo(
        () => items.reduce((sum, item) => sum + item.price * item.qty, 0),
        [items],
    );
    const service = useMemo(
        () => Math.round(subtotal * (serviceRate / 100) * 100) / 100,
        [subtotal, serviceRate],
    );
    const tax = useMemo(
        () => Math.round(subtotal * (vatRate / 100) * 100) / 100,
        [subtotal, vatRate],
    );
    const total = useMemo(() => subtotal + service + tax, [subtotal, service, tax]);
    const cartCount = useMemo(
        () => items.reduce((sum, item) => sum + item.qty, 0),
        [items],
    );

    const value = useMemo(
        () => ({
            items,
            open,
            setOpen,
            toast,
            setToast,
            addItem,
            removeItem,
            changeQty,
            setQty,
            clearCart,
            subtotal,
            service,
            tax,
            total,
            cartCount,
            orderingEnabled,
            vatRate,
            serviceRate,
        }),
        [
            items,
            open,
            toast,
            addItem,
            removeItem,
            changeQty,
            setQty,
            clearCart,
            subtotal,
            service,
            tax,
            total,
            cartCount,
            orderingEnabled,
            vatRate,
            serviceRate,
        ],
    );

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
    const ctx = useContext(CartContext);
    if (!ctx) {
        throw new Error('useCart must be used within CartProvider');
    }
    return ctx;
}
