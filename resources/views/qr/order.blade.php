<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Table {{ $table->code }} · {{ $settings->restaurant_name ?? 'Bynnas Restora' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f3ee;
            --surface: #ffffff;
            --ink: #1c1917;
            --muted: #78716c;
            --line: rgba(28, 25, 23, 0.1);
            --ember: #c2410c;
            --ember-deep: #9a3412;
            --green: #047857;
            --shadow: 0 10px 30px rgba(28, 25, 23, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DM Sans', system-ui, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(194, 65, 12, 0.08), transparent 40%),
                linear-gradient(180deg, #faf7f2 0%, var(--bg) 100%);
            color: var(--ink);
            min-height: 100vh;
        }
        .wrap { max-width: 920px; margin: 0 auto; padding: 16px 16px 110px; }
        .hero {
            background: linear-gradient(135deg, #1c1917 0%, #292524 55%, #431407 100%);
            color: #fff;
            border-radius: 22px;
            padding: 22px 20px;
            box-shadow: var(--shadow);
            margin-bottom: 16px;
        }
        .hero-top { display: flex; justify-content: space-between; gap: 12px; align-items: start; }
        .eyebrow { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.65); font-weight: 700; }
        .hero h1 { margin: 6px 0 4px; font-family: 'Cormorant Garamond', Georgia, serif; font-size: 34px; line-height: 1.05; }
        .hero p { margin: 0; color: rgba(255,255,255,0.75); font-size: 14px; }
        .table-chip {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            padding: 10px 12px;
            text-align: center;
            min-width: 84px;
        }
        .table-chip strong { display: block; font-size: 22px; font-family: 'Cormorant Garamond', Georgia, serif; }
        .table-chip span { font-size: 11px; color: rgba(255,255,255,0.7); }
        .cats {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 4px 0 12px;
            scrollbar-width: none;
        }
        .cats::-webkit-scrollbar { display: none; }
        .cat {
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--muted);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
        }
        .cat.active { background: var(--ink); color: #fff; border-color: var(--ink); }
        .grid { display: grid; gap: 12px; }
        .item {
            display: grid;
            grid-template-columns: 88px 1fr auto;
            gap: 12px;
            align-items: center;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 1px 2px rgba(28,25,23,0.04);
        }
        .item img {
            width: 88px; height: 88px; object-fit: cover; border-radius: 14px; background: #e7e5e4;
        }
        .item h3 { margin: 0 0 4px; font-size: 16px; }
        .item p { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .price { margin-top: 8px; font-weight: 700; color: var(--ember-deep); }
        .add {
            border: 0;
            background: var(--ember);
            color: #fff;
            border-radius: 12px;
            height: 40px;
            padding: 0 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .add:hover { background: var(--ember-deep); }
        .empty { text-align: center; color: var(--muted); padding: 40px 12px; }
        .disabled-banner {
            background: #fff7ed; border: 1px solid #fdba74; color: #9a3412;
            border-radius: 14px; padding: 12px 14px; margin-bottom: 12px; font-size: 13px;
        }
        .cart-bar {
            position: fixed; left: 0; right: 0; bottom: 0;
            background: rgba(28,25,23,0.96); color: #fff;
            padding: 14px 16px; display: none; z-index: 40;
        }
        .cart-bar.open { display: block; }
        .cart-bar-inner {
            max-width: 920px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .cart-bar button {
            border: 0; background: var(--ember); color: #fff;
            border-radius: 12px; height: 44px; padding: 0 18px; font-weight: 700; cursor: pointer;
        }
        .sheet {
            position: fixed; inset: 0; z-index: 50; display: none;
            background: rgba(0,0,0,0.45);
        }
        .sheet.open { display: block; }
        .sheet-panel {
            position: absolute; left: 0; right: 0; bottom: 0;
            background: #fff; border-radius: 22px 22px 0 0;
            max-height: 88vh; overflow: auto; padding: 18px 16px 24px;
        }
        .sheet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .sheet-head h2 { margin: 0; font-size: 20px; }
        .icon-btn { border: 1px solid var(--line); background: #fff; border-radius: 10px; width: 36px; height: 36px; cursor: pointer; }
        .line {
            display: flex; justify-content: space-between; gap: 10px; align-items: center;
            padding: 10px 0; border-bottom: 1px solid var(--line);
        }
        .qty {
            display: inline-flex; align-items: center; gap: 8px;
            border: 1px solid var(--line); border-radius: 999px; padding: 4px 8px;
        }
        .qty button { border: 0; background: transparent; font-size: 16px; cursor: pointer; width: 24px; }
        .totals { display: grid; gap: 6px; margin: 12px 0; font-size: 13px; color: var(--muted); }
        .totals div { display: flex; justify-content: space-between; }
        .totals .grand { color: var(--ink); font-weight: 800; font-size: 16px; padding-top: 6px; border-top: 1px dashed var(--line); }
        .field {
            width: 100%; height: 44px; border: 1px solid var(--line); border-radius: 12px;
            padding: 0 12px; margin-bottom: 10px; font: inherit;
        }
        textarea.field { height: 84px; padding: 10px 12px; resize: vertical; }
        .submit {
            width: 100%; height: 48px; border: 0; border-radius: 14px;
            background: var(--ember); color: #fff; font-weight: 800; font-size: 15px; cursor: pointer;
        }
        .submit:disabled { opacity: 0.6; cursor: not-allowed; }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 12px; padding: 10px 12px; margin-bottom: 10px; font-size: 13px; }
        .success {
            text-align: center; padding: 20px 10px;
        }
        .success strong { display: block; font-size: 22px; margin: 8px 0; color: var(--green); }
        @media (min-width: 720px) {
            .grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <header class="hero">
        <div class="hero-top">
            <div>
                <div class="eyebrow">QR Table Order</div>
                <h1>{{ $settings->restaurant_name ?? 'Bynnas Restora' }}</h1>
                <p>Order from your phone — kitchen gets it instantly.</p>
            </div>
            <div class="table-chip">
                <strong>{{ $table->code }}</strong>
                <span>{{ $table->zone }}</span>
            </div>
        </div>
    </header>

    @unless ($orderingEnabled)
        <div class="disabled-banner">Online ordering is temporarily disabled. Please call a waiter.</div>
    @endunless

    <div class="cats" id="cats">
        <button type="button" class="cat active" data-cat="all">All</button>
        @foreach ($categories as $cat)
            <button type="button" class="cat" data-cat="{{ $cat }}">{{ $cat }}</button>
        @endforeach
    </div>

    <div class="grid" id="menuGrid"></div>
    <div class="empty" id="emptyState" hidden>No dishes in this category.</div>
</div>

<div class="cart-bar" id="cartBar">
    <div class="cart-bar-inner">
        <div>
            <strong id="cartCountLabel">0 items</strong>
            <div id="cartTotalLabel">৳ 0.00</div>
        </div>
        <button type="button" id="openCartBtn">View cart</button>
    </div>
</div>

<div class="sheet" id="cartSheet">
    <div class="sheet-panel">
        <div class="sheet-head">
            <h2>Your order</h2>
            <button type="button" class="icon-btn" id="closeCartBtn" aria-label="Close">✕</button>
        </div>
        <div id="cartLines"></div>
        <div class="totals">
            <div><span>Subtotal</span><strong id="sumSubtotal">৳ 0.00</strong></div>
            <div><span>Service ({{ number_format($tax['service_charge_rate'], 1) }}%)</span><strong id="sumService">৳ 0.00</strong></div>
            <div><span>{{ $tax['tax_name'] }} ({{ number_format($tax['vat_rate'], 1) }}%)</span><strong id="sumTax">৳ 0.00</strong></div>
            <div class="grand"><span>Total</span><strong id="sumTotal">৳ 0.00</strong></div>
        </div>
        <form id="checkoutForm">
            <div id="formError" class="error" hidden></div>
            <input class="field" name="customer_name" placeholder="Your name *" required maxlength="120">
            <input class="field" name="customer_phone" placeholder="Phone (optional)" maxlength="40">
            <textarea class="field" name="notes" placeholder="Notes for kitchen (optional)" maxlength="500"></textarea>
            <button class="submit" type="submit" id="placeOrderBtn" {{ $orderingEnabled ? '' : 'disabled' }}>
                Place order for Table {{ $table->code }}
            </button>
        </form>
        <div class="success" id="successBox" hidden>
            <div class="eyebrow" style="color:var(--muted)">Order sent</div>
            <strong id="successNumber">—</strong>
            <p>We’ll prepare it for Table <b>{{ $table->code }}</b>.</p>
            <button type="button" class="submit" id="newOrderBtn" style="margin-top:12px;background:var(--ink)">Order more</button>
        </div>
    </div>
</div>

<script>
window.qrOrder = {
    token: @json($table->qr_token),
    table: @json($table->code),
    orderUrl: @json(route('qr.order.store', $table->qr_token)),
    orderingEnabled: @json($orderingEnabled),
    tax: @json($tax),
    currency: @json($settings->currency_symbol ?: '৳'),
    items: @json($menuItems)
};
</script>
<script>
(function () {
    var data = window.qrOrder || { items: [], tax: {}, currency: '৳' };
    var cart = {}; // id -> qty
    var activeCat = 'all';
    var money = function (n) {
        return (data.currency || '৳') + ' ' + (Number(n) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    function totals() {
        var subtotal = 0;
        Object.keys(cart).forEach(function (id) {
            var item = data.items.find(function (x) { return String(x.id) === String(id); });
            if (item) subtotal += item.price * cart[id];
        });
        var service = subtotal * ((data.tax.service_charge_rate || 0) / 100);
        var tax = subtotal * ((data.tax.vat_rate || 0) / 100);
        return { subtotal: subtotal, service: service, tax: tax, total: subtotal + service + tax, count: Object.values(cart).reduce(function (a, b) { return a + b; }, 0) };
    }

    function renderMenu() {
        var grid = document.getElementById('menuGrid');
        var empty = document.getElementById('emptyState');
        var list = data.items.filter(function (item) {
            return activeCat === 'all' || item.category === activeCat;
        });
        empty.hidden = list.length > 0;
        grid.innerHTML = list.map(function (item) {
            return '<article class="item" data-id="' + item.id + '">'
                + '<img src="' + item.image + '" alt="">'
                + '<div><h3>' + item.name + '</h3><p>' + (item.description || '') + '</p><div class="price">' + money(item.price) + '</div></div>'
                + '<button type="button" class="add"' + (data.orderingEnabled ? '' : ' disabled') + '>Add</button>'
                + '</article>';
        }).join('');
        grid.querySelectorAll('.add').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.closest('.item').getAttribute('data-id');
                cart[id] = (cart[id] || 0) + 1;
                renderCart();
            });
        });
    }

    function renderCart() {
        var t = totals();
        var bar = document.getElementById('cartBar');
        bar.classList.toggle('open', t.count > 0);
        document.getElementById('cartCountLabel').textContent = t.count + (t.count === 1 ? ' item' : ' items');
        document.getElementById('cartTotalLabel').textContent = money(t.total);

        var lines = document.getElementById('cartLines');
        var html = '';
        Object.keys(cart).forEach(function (id) {
            var item = data.items.find(function (x) { return String(x.id) === String(id); });
            if (!item) return;
            html += '<div class="line" data-id="' + id + '">'
                + '<div><strong>' + item.name + '</strong><div style="color:var(--muted);font-size:12px">' + money(item.price) + '</div></div>'
                + '<div class="qty">'
                + '<button type="button" data-act="-">−</button><span>' + cart[id] + '</span><button type="button" data-act="+">+</button>'
                + '</div></div>';
        });
        lines.innerHTML = html || '<p class="empty">Cart is empty.</p>';
        lines.querySelectorAll('.qty button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.closest('.line').getAttribute('data-id');
                if (btn.getAttribute('data-act') === '+') cart[id] += 1;
                else {
                    cart[id] -= 1;
                    if (cart[id] <= 0) delete cart[id];
                }
                renderCart();
            });
        });

        document.getElementById('sumSubtotal').textContent = money(t.subtotal);
        document.getElementById('sumService').textContent = money(t.service);
        document.getElementById('sumTax').textContent = money(t.tax);
        document.getElementById('sumTotal').textContent = money(t.total);
    }

    document.getElementById('cats').addEventListener('click', function (e) {
        var btn = e.target.closest('.cat');
        if (!btn) return;
        document.querySelectorAll('.cat').forEach(function (c) { c.classList.remove('active'); });
        btn.classList.add('active');
        activeCat = btn.getAttribute('data-cat');
        renderMenu();
    });

    document.getElementById('openCartBtn').addEventListener('click', function () {
        document.getElementById('cartSheet').classList.add('open');
        document.getElementById('checkoutForm').hidden = false;
        document.getElementById('successBox').hidden = true;
    });
    document.getElementById('closeCartBtn').addEventListener('click', function () {
        document.getElementById('cartSheet').classList.remove('open');
    });
    document.getElementById('cartSheet').addEventListener('click', function (e) {
        if (e.target.id === 'cartSheet') e.currentTarget.classList.remove('open');
    });

    document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!data.orderingEnabled) return;
        var err = document.getElementById('formError');
        err.hidden = true;
        var t = totals();
        if (t.count < 1) {
            err.textContent = 'Add at least one item.';
            err.hidden = false;
            return;
        }
        var form = e.target;
        var btn = document.getElementById('placeOrderBtn');
        btn.disabled = true;
        btn.textContent = 'Sending…';
        try {
            var res = await fetch(data.orderUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    customer_name: form.customer_name.value,
                    customer_phone: form.customer_phone.value,
                    notes: form.notes.value,
                    items: Object.keys(cart).map(function (id) {
                        return { menu_item_id: Number(id), quantity: cart[id] };
                    })
                })
            });
            var payload = await res.json().catch(function () { return {}; });
            if (!res.ok) throw new Error(payload.message || 'Could not place order.');
            cart = {};
            renderCart();
            form.hidden = true;
            document.getElementById('successBox').hidden = false;
            document.getElementById('successNumber').textContent = payload.order_number;
            form.reset();
        } catch (ex) {
            err.textContent = ex.message || 'Order failed.';
            err.hidden = false;
        } finally {
            btn.disabled = !data.orderingEnabled;
            btn.textContent = 'Place order for Table ' + data.table;
        }
    });

    document.getElementById('newOrderBtn').addEventListener('click', function () {
        document.getElementById('successBox').hidden = true;
        document.getElementById('checkoutForm').hidden = false;
        document.getElementById('cartSheet').classList.remove('open');
    });

    renderMenu();
    renderCart();
})();
</script>
</body>
</html>
