import { useCart } from '../../context/CartContext';

export default function CartToast() {
    const { toast } = useCart();
    if (!toast) return null;

    return (
        <div className="pointer-events-none fixed bottom-6 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-ink px-4 py-2.5 text-sm font-semibold text-white shadow-lg">
            {toast}
        </div>
    );
}
