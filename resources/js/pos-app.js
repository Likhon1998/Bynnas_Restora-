(function () {
    var config = window.POS_CONFIG || {};
    var heldOrders = config.heldOrders || [];
    var vatRate = Number(config.vatRate != null ? config.vatRate : 7) / 100;
    var serviceRate = Number(config.serviceRate != null ? config.serviceRate : 5) / 100;
    var taxName = config.taxName || 'VAT';
    var cart = [];
    var discount = 0;
    var discountPct = null;
    var noteEditIndex = null;
    var selectedMod = '';
    var toastTimer = null;

    var cartList = document.getElementById('cartList');
    var hidden = document.getElementById('cartHiddenInputs');
    var flash = document.getElementById('posFlash');
    if (flash) setTimeout(function () { flash.remove(); }, 3500);

    var serviceRateLabel = document.getElementById('serviceRateLabel');
    var taxRateLabel = document.getElementById('taxRateLabel');
    var taxNameLabel = document.getElementById('taxNameLabel');
    if (serviceRateLabel) serviceRateLabel.textContent = String(Number(config.serviceRate != null ? config.serviceRate : 5));
    if (taxRateLabel) taxRateLabel.textContent = String(Number(config.vatRate != null ? config.vatRate : 7));
    if (taxNameLabel) taxNameLabel.textContent = 'Tax (' + taxName;

    function money(n) {
        return '৳ ' + Number(n).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function cartKey(item) {
        return item.id + '::' + (item.note || '');
    }

    function toast(msg) {
        var el = document.getElementById('posToast');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.add('hidden'); }, 1800);
    }

    function totals() {
        var subtotal = cart.reduce(function (s, i) { return s + i.price * i.qty; }, 0);
        if (discountPct) discount = +(subtotal * (discountPct / 100)).toFixed(2);
        var service = +(subtotal * serviceRate).toFixed(2);
        var tax = +(subtotal * vatRate).toFixed(2);
        var total = Math.max(0, subtotal + service + tax - discount);
        return { subtotal: subtotal, service: service, tax: tax, total: total };
    }

    function itemCount() {
        return cart.reduce(function (s, i) { return s + i.qty; }, 0);
    }

    function render() {
        var t = totals();
        if (!cart.length) {
            cartList.innerHTML = '<li class="cart-empty">No items yet. Tap + on a product to add.</li>';
        } else {
            cartList.innerHTML = cart.map(function (item, idx) {
                var mods = item.note ? '<div class="mods">• ' + escapeHtml(item.note) + '</div>' : '';
                return '<li class="cart-line">'
                    + '<div class="qty">' + item.qty + '×</div>'
                    + '<div class="details">'
                    + '<strong>' + escapeHtml(item.name) + '</strong>'
                    + mods
                    + '<div class="qty-controls">'
                    + '<button type="button" class="dec" data-idx="' + idx + '" aria-label="Decrease">−</button>'
                    + '<span>' + item.qty + '</span>'
                    + '<button type="button" class="inc" data-idx="' + idx + '" aria-label="Increase">+</button>'
                    + '<button type="button" class="note-btn" data-idx="' + idx + '">Note</button>'
                    + '</div></div>'
                    + '<div class="right">'
                    + '<span class="unit">' + money(item.price) + '</span>'
                    + '<span class="line-total">' + money(item.price * item.qty) + '</span>'
                    + '<button type="button" class="rm" data-idx="' + idx + '" aria-label="Remove">×</button>'
                    + '</div></li>';
            }).join('');
        }

        document.getElementById('subtotalLabel').textContent = money(t.subtotal);
        document.getElementById('serviceLabel').textContent = money(t.service);
        document.getElementById('taxLabel').textContent = money(t.tax);
        document.getElementById('totalLabel').textContent = money(t.total);
        document.getElementById('payAmount').textContent = money(t.total);
        document.getElementById('statItems').textContent = String(itemCount());
        document.getElementById('statTotal').textContent = money(t.total);

        var discountRow = document.getElementById('discountRow');
        var discountLabel = document.getElementById('discountLabel');
        if (discount > 0) {
            discountRow.classList.remove('hidden');
            discountLabel.textContent = '-' + money(discount);
        } else {
            discountRow.classList.add('hidden');
        }

        document.getElementById('serviceChargeInput').value = t.service;
        document.getElementById('taxInput').value = t.tax;
        document.getElementById('discountInput').value = discount;

        hidden.innerHTML = cart.map(function (item, i) {
            var note = item.note ? '<input type="hidden" name="items[' + i + '][note]" value="' + escapeHtml(item.note) + '">' : '';
            return '<input type="hidden" name="items[' + i + '][menu_item_id]" value="' + item.id + '">'
                + '<input type="hidden" name="items[' + i + '][quantity]" value="' + item.qty + '">'
                + note;
        }).join('');
    }

    function addItem(id, name, price, note) {
        note = note || '';
        var key = String(id) + '::' + note;
        var found = cart.find(function (c) { return cartKey(c) === key; });
        if (found) found.qty += 1;
        else cart.push({ id: String(id), name: name, price: Number(price), qty: 1, note: note });
        render();
        toast(name + ' added');
    }

    function openNoteModal(idx) {
        var item = cart[idx];
        if (!item) return;
        noteEditIndex = idx;
        selectedMod = item.note || '';
        document.getElementById('itemModalTitle').textContent = item.name;
        document.getElementById('itemModalPrice').textContent = money(item.price);
        document.getElementById('itemModalNote').value = item.note || '';
        document.querySelectorAll('#modifierChips button').forEach(function (b) {
            b.classList.toggle('active', (b.dataset.mod || '') === selectedMod);
        });
        document.getElementById('itemModal').classList.remove('hidden');
    }

    document.getElementById('menuGrid').addEventListener('click', function (e) {
        var card = e.target.closest('.product-card');
        if (!card) return;
        addItem(card.dataset.id, card.dataset.name, card.dataset.price, '');
    });

    document.getElementById('modifierChips').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        document.querySelectorAll('#modifierChips button').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        selectedMod = btn.dataset.mod || '';
        document.getElementById('itemModalNote').value = selectedMod;
    });

    document.getElementById('cancelItemModal').addEventListener('click', function () {
        document.getElementById('itemModal').classList.add('hidden');
        noteEditIndex = null;
    });
    document.getElementById('confirmItemModal').addEventListener('click', function () {
        if (noteEditIndex === null || !cart[noteEditIndex]) return;
        cart[noteEditIndex].note = (document.getElementById('itemModalNote').value || selectedMod || '').trim();
        document.getElementById('itemModal').classList.add('hidden');
        noteEditIndex = null;
        render();
    });

    cartList.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var idx = Number(btn.dataset.idx);
        if (Number.isNaN(idx) || !cart[idx]) return;
        if (btn.classList.contains('rm')) {
            cart.splice(idx, 1);
            render();
            return;
        }
        if (btn.classList.contains('inc')) {
            cart[idx].qty += 1;
            render();
            return;
        }
        if (btn.classList.contains('dec')) {
            if (cart[idx].qty <= 1) cart.splice(idx, 1);
            else cart[idx].qty -= 1;
            render();
            return;
        }
        if (btn.classList.contains('note-btn')) {
            openNoteModal(idx);
        }
    });

    document.getElementById('clearCart').addEventListener('click', function () {
        if (cart.length && !confirm('Clear all items from this order?')) return;
        cart = [];
        discount = 0;
        discountPct = null;
        document.getElementById('resumeOrderId').value = '';
        document.querySelectorAll('#discountPresets button').forEach(function (b) { b.classList.remove('active'); });
        render();
    });

    var activeCategory = 'all';
    var activeFilter = 'all';
    var searchQuery = '';

    function applyFilters() {
        var cards = Array.from(document.querySelectorAll('#menuGrid .product-card'));
        cards.forEach(function (card) {
            var ok = true;
            if (searchQuery && card.dataset.name.toLowerCase().indexOf(searchQuery) === -1) ok = false;
            if (activeCategory !== 'all' && card.dataset.category !== activeCategory) ok = false;
            if (activeFilter === 'bestseller' && card.dataset.bestseller !== '1') ok = false;
            if (activeFilter === 'new' && card.dataset.new !== '1') ok = false;
            if (activeFilter === 'spicy' && card.dataset.spicy !== '1') ok = false;
            if (activeFilter === 'vegetarian' && card.dataset.vegetarian !== '1') ok = false;
            card.style.display = ok ? '' : 'none';
        });

        var sort = document.getElementById('sortSelect').value;
        var grid = document.getElementById('menuGrid');
        var visible = cards.filter(function (c) { return c.style.display !== 'none'; });
        visible.sort(function (a, b) {
            if (sort === 'price-asc') return Number(a.dataset.price) - Number(b.dataset.price);
            if (sort === 'price-desc') return Number(b.dataset.price) - Number(a.dataset.price);
            if (sort === 'name') return a.dataset.name.localeCompare(b.dataset.name);
            return Number(b.dataset.popular) - Number(a.dataset.popular);
        });
        visible.forEach(function (c) { grid.appendChild(c); });
    }

    document.getElementById('catTabs').addEventListener('click', function (e) {
        var btn = e.target.closest('.cat-tab');
        if (!btn || btn.id === 'moreCatsBtn') return;
        document.querySelectorAll('#catTabs .cat-tab').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        activeCategory = btn.dataset.category;
        applyFilters();
    });

    var moreBtn = document.getElementById('moreCatsBtn');
    if (moreBtn) {
        moreBtn.addEventListener('click', function () {
            document.querySelectorAll('#catTabs .cat-tab.hidden-cat').forEach(function (tab) {
                tab.classList.remove('hidden-cat');
            });
            this.style.display = 'none';
        });
    }

    document.getElementById('filterPills').addEventListener('click', function (e) {
        var btn = e.target.closest('.filter-pill');
        if (!btn) return;
        document.querySelectorAll('#filterPills .filter-pill').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        applyFilters();
    });

    document.getElementById('sortSelect').addEventListener('change', applyFilters);

    document.getElementById('globalSearch').addEventListener('input', function () {
        searchQuery = (this.value || '').trim().toLowerCase();
        applyFilters();
    });

    document.querySelector('.scan-btn').addEventListener('click', function () {
        document.getElementById('globalSearch').focus();
    });

    function setType(type) {
        document.getElementById('orderType').value = type;
        document.querySelectorAll('#serviceType button').forEach(function (b) {
            b.classList.toggle('active', b.dataset.type === type);
        });
    }

    document.getElementById('serviceType').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        setType(btn.dataset.type);
    });

    function syncTable(id, code) {
        document.getElementById('tableIdInput').value = id;
        document.getElementById('statTable').textContent = 'Table ' + code;
        var top = document.getElementById('topTableSelect');
        if (top) top.value = id;
    }

    document.getElementById('topTableSelect').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        syncTable(this.value, opt.dataset.code);
    });

    document.getElementById('guestCount').addEventListener('input', function () {
        document.getElementById('guestCountInput').value = this.value;
    });

    document.getElementById('tablePickerBtn').addEventListener('click', function () {
        document.getElementById('tableModal').classList.remove('hidden');
    });
    document.getElementById('cancelTableModal').addEventListener('click', function () {
        document.getElementById('tableModal').classList.add('hidden');
    });
    document.getElementById('confirmTableBtn').addEventListener('click', function () {
        var sel = document.getElementById('modalTableSelect');
        var opt = sel.options[sel.selectedIndex];
        syncTable(sel.value, opt.dataset.code);
        document.getElementById('tableModal').classList.add('hidden');
    });

    function syncCustomer(id, name, phone) {
        document.getElementById('customerSelect').value = id || '';
        document.getElementById('customerName').value = name || '';
        var phoneInput = document.getElementById('customerPhone');
        if (phoneInput) phoneInput.value = phone || '';
        document.getElementById('statCustomer').textContent = name || 'Walk-in';
        var modalName = document.getElementById('modalCustomerName');
        var modalPhone = document.getElementById('modalCustomerPhone');
        if (modalName) modalName.value = name || '';
        if (modalPhone) modalPhone.value = phone || '';
    }

    document.getElementById('customerPickerBtn').addEventListener('click', function () {
        document.getElementById('modalCustomerSelect').value = document.getElementById('customerSelect').value;
        document.getElementById('customerModal').classList.remove('hidden');
    });
    document.getElementById('cancelCustomerModal').addEventListener('click', function () {
        document.getElementById('customerModal').classList.add('hidden');
    });
    document.getElementById('confirmCustomerBtn').addEventListener('click', function () {
        var sel = document.getElementById('modalCustomerSelect');
        var opt = sel.options[sel.selectedIndex];
        var name = sel.value ? (opt.dataset.name || opt.text) : '';
        var phone = sel.value ? (opt.dataset.phone || '') : '';
        syncCustomer(sel.value, name, phone);
        document.getElementById('customerModal').classList.add('hidden');
    });

    document.getElementById('notifBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById('notifPanel').classList.toggle('hidden');
    });
    document.addEventListener('click', function () {
        document.getElementById('notifPanel').classList.add('hidden');
    });
    document.getElementById('notifPanel').addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.querySelectorAll('.resume-held').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var order = heldOrders.find(function (o) { return String(o.id) === String(btn.dataset.id); });
            if (!order) return;
            cart = order.items.map(function (item) {
                return {
                    id: String(item.menu_item_id || ''),
                    name: item.name,
                    price: Number(item.price),
                    qty: Number(item.qty),
                    note: item.note || '',
                };
            });
            discount = Number(order.discount_amount || 0);
            document.getElementById('resumeOrderId').value = order.id;
            document.getElementById('orderNumberLabel').textContent = 'Order #' + order.order_number;
            if (order.promo_code) document.getElementById('promoCode').value = order.promo_code;
            setType(order.type === 'walkin' || order.type === 'qr' ? 'dinein' : order.type);
            if (order.table_id && order.table_code) syncTable(order.table_id, order.table_code);
            if (order.guest_count) {
                document.getElementById('guestCount').value = order.guest_count;
                document.getElementById('guestCountInput').value = order.guest_count;
            }
            syncCustomer(order.customer_id || '', order.customer_name || '', order.customer_phone || '');
            document.getElementById('notifPanel').classList.add('hidden');
            render();
            toast('Held order loaded');
        });
    });

    document.getElementById('payMethods').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        document.querySelectorAll('#payMethods button').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('paymentMethod').value = btn.dataset.pay;
    });

    document.getElementById('applyPromo').addEventListener('click', function () {
        var code = (document.getElementById('promoCode').value || '').trim().toUpperCase();
        if (code === 'BYNNAS10') {
            discountPct = 10;
            toast('Promo BYNNAS10 applied');
        } else if (code) {
            discount = 0;
            discountPct = null;
            toast('Invalid promo code');
        } else {
            discount = 0;
            discountPct = null;
        }
        render();
    });

    document.getElementById('discountPresets').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        document.querySelectorAll('#discountPresets button').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        if (btn.dataset.pct === 'custom') {
            var val = prompt('Enter custom discount amount (৳):', String(discount || 0));
            if (val === null) return;
            discount = Math.max(0, Number(val) || 0);
            discountPct = null;
        } else {
            discountPct = Number(btn.dataset.pct);
        }
        render();
    });

    document.getElementById('payBtn').addEventListener('click', function () {
        if (!cart.length) { toast('Add at least one menu item'); return; }
        var nameEl = document.getElementById('modalCustomerName');
        var phoneEl = document.getElementById('modalCustomerPhone');
        if (nameEl) nameEl.value = document.getElementById('customerName').value || '';
        if (phoneEl) phoneEl.value = document.getElementById('customerPhone').value || '';
        document.getElementById('payModal').classList.remove('hidden');
    });
    document.getElementById('cancelPayModal').addEventListener('click', function () {
        document.getElementById('payModal').classList.add('hidden');
    });
    document.getElementById('confirmPayBtn').addEventListener('click', function () {
        var name = (document.getElementById('modalCustomerName').value || '').trim();
        var phone = (document.getElementById('modalCustomerPhone').value || '').trim();
        document.getElementById('customerName').value = name;
        document.getElementById('customerPhone').value = phone;
        document.getElementById('statCustomer').textContent = name || 'Walk-in';
        document.getElementById('orderNotes').value = document.getElementById('modalNotes').value;
        document.getElementById('posAction').value = 'pay';
        document.getElementById('payModal').classList.add('hidden');
        document.getElementById('posForm').requestSubmit();
    });

    document.getElementById('posForm').addEventListener('submit', function (e) {
        if (!cart.length) {
            e.preventDefault();
            toast('Add at least one menu item');
            return;
        }
        if (e.submitter && e.submitter.dataset.action) {
            document.getElementById('posAction').value = e.submitter.dataset.action;
        }
    });

    document.getElementById('copyOrderBtn').addEventListener('click', function () {
        var text = document.getElementById('orderNumberLabel').textContent.replace('Order #', '');
        if (navigator.clipboard) navigator.clipboard.writeText(text);
        toast('Order number copied');
    });

    document.getElementById('printBtn').addEventListener('click', function () {
        if (!cart.length) { toast('Add items before printing'); return; }
        window.print();
    });

    var fsBtn = document.getElementById('fullscreenBtn');
    var fsEnter = document.getElementById('fsEnterIcon');
    var fsExit = document.getElementById('fsExitIcon');
    var shell = document.querySelector('.pos-shell');

    function isFullscreen() {
        return document.fullscreenElement || document.webkitFullscreenElement;
    }

    function syncFullscreenIcons() {
        var on = !!isFullscreen();
        if (fsEnter) fsEnter.classList.toggle('hidden', on);
        if (fsExit) fsExit.classList.toggle('hidden', !on);
        if (fsBtn) fsBtn.title = on ? 'Exit full page' : 'Full page';
    }

    if (fsBtn && shell) {
        fsBtn.addEventListener('click', function () {
            if (isFullscreen()) {
                (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            } else if (shell.requestFullscreen) {
                shell.requestFullscreen();
            } else if (shell.webkitRequestFullscreen) {
                shell.webkitRequestFullscreen();
            }
        });
        document.addEventListener('fullscreenchange', syncFullscreenIcons);
        document.addEventListener('webkitfullscreenchange', syncFullscreenIcons);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'F11') {
            e.preventDefault();
            if (fsBtn) fsBtn.click();
        }
    });

    function tickClock() {
        var now = new Date();
        document.getElementById('statTime').textContent = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
    tickClock();
    setInterval(tickClock, 30000);

    applyFilters();
    render();
})();
