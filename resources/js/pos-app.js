(function () {
    var config = window.POS_CONFIG || {};
    var heldOrders = config.heldOrders || [];
    var defaultVatRate = Number(config.vatRate != null ? config.vatRate : 0);
    var defaultServiceRate = Number(config.serviceRate != null ? config.serviceRate : 0);
    var taxName = config.taxName || 'VAT';
    var applyService = config.applyServiceDefault === true;
    var applyTax = config.applyTaxDefault === true;
    var serviceRatePct = defaultServiceRate;
    var vatRatePct = defaultVatRate;
    var cart = [];
    var lastKitchenMap = {};
    var sendingOrder = false;
    var discount = 0;
    var discountPct = null;
    var noteEditIndex = null;
    var selectedMod = '';
    var toastTimer = null;

    var cartList = document.getElementById('cartList');
    var hidden = document.getElementById('cartHiddenInputs');
    var flash = document.getElementById('posFlash');
    if (flash) setTimeout(function () { flash.remove(); }, 3500);

    var applyServiceToggle = document.getElementById('applyServiceToggle');
    var applyTaxToggle = document.getElementById('applyTaxToggle');
    var serviceRateField = document.getElementById('serviceRateField');
    var taxRateField = document.getElementById('taxRateField');
    var taxNameLabel = document.getElementById('taxNameLabel');
    if (taxNameLabel) taxNameLabel.textContent = taxName;
    if (serviceRateField) serviceRateField.value = String(serviceRatePct);
    if (taxRateField) taxRateField.value = String(vatRatePct);
    if (applyServiceToggle) applyServiceToggle.checked = applyService;
    if (applyTaxToggle) applyTaxToggle.checked = applyTax;

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

    function setElText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function currentTableLabel() {
        if (lastTableCode && lastTableCode !== '—' && lastTableCode !== '-') {
            return 'Table ' + lastTableCode;
        }
        var top = document.getElementById('topTableSelect');
        if (top && top.value) {
            var opt = top.options[top.selectedIndex];
            var code = opt ? (opt.dataset.code || '') : '';
            return code ? ('Table ' + code) : 'No seat';
        }
        return 'No seat';
    }

    function currentCustomerLabel() {
        var nameEl = document.getElementById('customerName');
        var guestEl = document.getElementById('guestNameField');
        var name = ((nameEl && nameEl.value) || (guestEl && guestEl.value) || '').trim();
        return name || 'Walk-in';
    }

    function syncChargeUi() {
        if (serviceRateField) {
            serviceRateField.disabled = !applyService;
            serviceRateField.value = String(serviceRatePct);
        }
        if (taxRateField) {
            taxRateField.disabled = !applyTax;
            taxRateField.value = String(vatRatePct);
        }
        var serviceRow = document.getElementById('serviceChargeRow');
        var taxRow = document.getElementById('taxChargeRow');
        if (serviceRow) serviceRow.classList.toggle('is-on', applyService);
        if (taxRow) taxRow.classList.toggle('is-on', applyTax);
    }

    function totals() {
        var subtotal = cart.reduce(function (s, i) { return s + i.price * i.qty; }, 0);
        if (discountPct) discount = +(subtotal * (discountPct / 100)).toFixed(2);
        var service = applyService ? +(subtotal * (serviceRatePct / 100)).toFixed(2) : 0;
        var tax = applyTax ? +(subtotal * (vatRatePct / 100)).toFixed(2) : 0;
        var total = Math.max(0, subtotal + service + tax - discount);
        return { subtotal: subtotal, service: service, tax: tax, total: total };
    }

    function itemCount() {
        return cart.reduce(function (s, i) { return s + i.qty; }, 0);
    }

    var notePresets = ['No Butter', 'Extra Cheese', 'No Onion', 'Extra Spicy'];

    function cartLineHtml(item, idx, withNoteField) {
        var mods = item.note ? '<div class="mods">• ' + escapeHtml(item.note) + '</div>' : '';
        var extra = '';
        if (withNoteField) {
            extra = '<input type="text" class="field cart-line-note" data-idx="' + idx + '" value="' + escapeHtml(item.note || '') + '" placeholder="Add note, e.g. no onion">'
                + '<div class="cart-note-chips">'
                + notePresets.map(function (mod) {
                    return '<button type="button" class="note-chip' + (item.note === mod ? ' active' : '') + '" data-idx="' + idx + '" data-mod="' + escapeHtml(mod) + '">' + escapeHtml(mod) + '</button>';
                }).join('')
                + '</div>';
        }
        return '<li class="cart-line">'
            + '<div class="qty">' + item.qty + '×</div>'
            + '<div class="details">'
            + '<strong>' + escapeHtml(item.name) + '</strong>'
            + (withNoteField ? '' : mods)
            + '<div class="qty-controls">'
            + '<button type="button" class="dec" data-idx="' + idx + '" aria-label="Decrease">−</button>'
            + '<span>' + item.qty + '</span>'
            + '<button type="button" class="inc" data-idx="' + idx + '" aria-label="Increase">+</button>'
            + (withNoteField ? '' : '<button type="button" class="note-btn" data-idx="' + idx + '">Note</button>')
            + '</div></div>'
            + '<div class="right">'
            + '<span class="unit">' + money(item.price) + '</span>'
            + '<span class="line-total">' + money(item.price * item.qty) + '</span>'
            + '<button type="button" class="rm" data-idx="' + idx + '" aria-label="Remove">×</button>'
            + '</div>'
            + extra
            + '</li>';
    }

    function renderCartModal() {
        var list = document.getElementById('cartModalList');
        var countEl = document.getElementById('cartModalCount');
        var subtotalEl = document.getElementById('cartModalSubtotal');
        if (!list) return;

        if (!cart.length) {
            list.innerHTML = '<li class="cart-modal-empty">No items in this order yet.</li>';
        } else {
            list.innerHTML = cart.map(function (item, idx) { return cartLineHtml(item, idx, true); }).join('');
        }
        if (countEl) {
            var count = itemCount();
            countEl.textContent = (count === 1 ? '1 item' : count + ' items') + ' · add a note on any item';
        }
        if (subtotalEl) subtotalEl.textContent = money(totals().subtotal);
    }

    function openCartModal() {
        renderCartModal();
        var modal = document.getElementById('cartModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeCartModal() {
        var modal = document.getElementById('cartModal');
        if (modal) modal.classList.add('hidden');
    }

    function handleCartLineClick(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var idx = Number(btn.dataset.idx);
        if (Number.isNaN(idx) || !cart[idx]) return;
        if (btn.classList.contains('rm')) {
            cart.splice(idx, 1);
            render();
            if (!cart.length) closeCartModal();
            return;
        }
        if (btn.classList.contains('inc')) {
            cart[idx].qty += 1;
            render();
            return;
        }
        if (btn.classList.contains('dec')) {
            if (cart[idx].qty <= 1) {
                cart.splice(idx, 1);
                if (!cart.length) closeCartModal();
            } else {
                cart[idx].qty -= 1;
            }
            render();
            return;
        }
        if (btn.classList.contains('note-chip')) {
            var current = (cart[idx].note || '');
            cart[idx].note = current === btn.dataset.mod ? '' : (btn.dataset.mod || '');
            render();
            return;
        }
        if (btn.classList.contains('note-btn')) {
            openNoteModal(idx);
        }
    }

    function render() {
        var t = totals();
        syncChargeUi();
        if (!cartList) return;
        if (!cart.length) {
            cartList.innerHTML = '<li class="cart-empty">No items yet. Tap + on a menu item to add.</li>';
        } else {
            cartList.innerHTML = cart.map(function (item, idx) { return cartLineHtml(item, idx); }).join('');
        }

        setElText('subtotalLabel', money(t.subtotal));
        setElText('serviceLabel', money(t.service));
        setElText('taxLabel', money(t.tax));
        setElText('totalLabel', money(t.total));
        setElText('payAmount', money(t.total));
        var cartCountLabel = document.getElementById('cartCountLabel');
        if (cartCountLabel) {
            var count = itemCount();
            cartCountLabel.textContent = count === 1 ? '1 item' : count + ' items';
        }
        var expandBtn = document.getElementById('expandCartBtn');
        if (expandBtn) expandBtn.disabled = false;
        renderCartModal();

        var discountRow = document.getElementById('discountRow');
        var discountLabel = document.getElementById('discountLabel');
        if (discountRow) {
            if (discount > 0) {
                discountRow.classList.remove('hidden');
                if (discountLabel) discountLabel.textContent = '−' + money(discount);
            } else {
                discountRow.classList.add('hidden');
            }
        }

        var serviceChargeInput = document.getElementById('serviceChargeInput');
        var taxInput = document.getElementById('taxInput');
        var discountInput = document.getElementById('discountInput');
        if (serviceChargeInput) serviceChargeInput.value = t.service;
        if (taxInput) taxInput.value = t.tax;
        if (discountInput) discountInput.value = discount;
        var applyServiceInput = document.getElementById('applyServiceInput');
        var applyTaxInput = document.getElementById('applyTaxInput');
        var serviceRateInput = document.getElementById('serviceRateInput');
        var taxRateInput = document.getElementById('taxRateInput');
        if (applyServiceInput) applyServiceInput.value = applyService ? '1' : '0';
        if (applyTaxInput) applyTaxInput.value = applyTax ? '1' : '0';
        if (serviceRateInput) serviceRateInput.value = serviceRatePct;
        if (taxRateInput) taxRateInput.value = vatRatePct;

        if (hidden) {
            hidden.innerHTML = cart.map(function (item, i) {
                var note = item.note ? '<input type="hidden" name="items[' + i + '][note]" value="' + escapeHtml(item.note) + '">' : '';
                return '<input type="hidden" name="items[' + i + '][menu_item_id]" value="' + item.id + '">'
                    + '<input type="hidden" name="items[' + i + '][quantity]" value="' + item.qty + '">'
                    + note;
            }).join('');
        }
        syncCheckoutMode();
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

    if (applyServiceToggle) {
        applyServiceToggle.addEventListener('change', function () {
            applyService = !!applyServiceToggle.checked;
            render();
            toast(applyService ? 'Service charge applied' : 'Service charge removed');
        });
    }
    if (applyTaxToggle) {
        applyTaxToggle.addEventListener('change', function () {
            applyTax = !!applyTaxToggle.checked;
            render();
            toast(applyTax ? (taxName + ' applied') : (taxName + ' removed'));
        });
    }
    if (serviceRateField) {
        serviceRateField.addEventListener('input', function () {
            var n = parseFloat(serviceRateField.value);
            serviceRatePct = isFinite(n) && n >= 0 ? Math.min(100, n) : 0;
            render();
        });
    }
    if (taxRateField) {
        taxRateField.addEventListener('input', function () {
            var n = parseFloat(taxRateField.value);
            vatRatePct = isFinite(n) && n >= 0 ? Math.min(100, n) : 0;
            render();
        });
    }

    var menuGrid = document.getElementById('menuGrid');
    if (menuGrid) {
        menuGrid.addEventListener('click', function (e) {
            var card = e.target.closest('.product-card');
            if (!card) return;
            addItem(card.dataset.id, card.dataset.name, card.dataset.price, '');
        });
    }

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

    if (cartList) cartList.addEventListener('click', handleCartLineClick);
    var cartModalList = document.getElementById('cartModalList');
    if (cartModalList) {
        cartModalList.addEventListener('click', handleCartLineClick);
        cartModalList.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || !e.target.classList.contains('cart-line-note')) return;
            e.preventDefault();
            e.stopPropagation();
            e.target.blur();
        });
        cartModalList.addEventListener('input', function (e) {
            var field = e.target.closest('.cart-line-note');
            if (!field) return;
            var idx = Number(field.dataset.idx);
            if (Number.isNaN(idx) || !cart[idx]) return;
            cart[idx].note = field.value;
        });
        cartModalList.addEventListener('blur', function (e) {
            var field = e.target.closest && e.target.closest('.cart-line-note');
            if (!field) return;
            var idx = Number(field.dataset.idx);
            if (Number.isNaN(idx) || !cart[idx]) return;
            cart[idx].note = (field.value || '').trim();
            render();
        }, true);
    }

    var expandBtnEl = document.getElementById('expandCartBtn');
    if (expandBtnEl) expandBtnEl.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openCartModal();
    });
    document.getElementById('closeCartModal').addEventListener('click', closeCartModal);
    document.getElementById('closeCartModalBtn').addEventListener('click', closeCartModal);
    document.getElementById('cartModal').addEventListener('click', function (e) {
        if (e.target.id === 'cartModal') closeCartModal();
    });

    document.getElementById('clearCart').addEventListener('click', function () {
        if (cart.length && !confirm('Clear all items from this order?')) return;
        cart = [];
        discount = 0;
        discountPct = null;
        applyService = config.applyServiceDefault === true;
        applyTax = config.applyTaxDefault === true;
        serviceRatePct = defaultServiceRate;
        vatRatePct = defaultVatRate;
        if (applyServiceToggle) applyServiceToggle.checked = applyService;
        if (applyTaxToggle) applyTaxToggle.checked = applyTax;
        document.getElementById('resumeOrderId').value = '';
        resetToNewOrder();
        document.querySelectorAll('#discountPresets button').forEach(function (b) { b.classList.remove('active'); });
        syncCustomer('', '', '');
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

    function escapeAttr(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function productCardHtml(item) {
        var fallback = 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80';
        var badge = item.badge
            ? '<span class="badge ' + escapeAttr(item.badge_key) + '">' + escapeHtml(item.badge) + '</span>'
            : '';
        var desc = item.description
            ? '<p class="ingredients">' + escapeHtml(item.description) + '</p>'
            : '<p class="ingredients muted">No ingredients listed</p>';
        return '<article class="product-card"'
            + ' data-id="' + escapeAttr(item.id) + '"'
            + ' data-name="' + escapeAttr(item.name) + '"'
            + ' data-price="' + escapeAttr(item.price) + '"'
            + ' data-category="' + escapeAttr(item.category || '') + '"'
            + ' data-popular="' + (item.is_popular ? '1' : '0') + '"'
            + ' data-bestseller="' + (item.is_bestseller ? '1' : '0') + '"'
            + ' data-new="' + (item.is_new ? '1' : '0') + '"'
            + ' data-spicy="' + (item.is_spicy ? '1' : '0') + '"'
            + ' data-vegetarian="' + (item.is_vegetarian ? '1' : '0') + '"'
            + ' data-favorite="' + (item.is_favorite ? '1' : '0') + '">'
            + '<div class="thumb"><img src="' + escapeAttr(item.image_url || fallback) + '" alt="' + escapeAttr(item.name) + '" loading="lazy" onerror="this.src=\'' + fallback + '\'">'
            + badge
            + '<span class="star ' + (item.is_favorite ? 'on' : '') + '" aria-hidden="true"></span></div>'
            + '<div class="info"><p class="name">' + escapeHtml(item.name) + '</p>' + desc
            + '<div class="foot"><span class="price">৳ ' + Number(item.price).toFixed(2) + '</span><span class="add-btn" aria-hidden="true">+</span></div></div></article>';
    }

    function refreshCatalog() {
        if (!config.catalogUrl) return;
        fetch(config.catalogUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var grid = document.getElementById('menuGrid');
                if (!grid || !data.items) return;
                if (!data.items.length) {
                    grid.innerHTML = '<div class="cart-empty" style="grid-column:1/-1">No available menu items.</div>';
                    return;
                }
                grid.innerHTML = data.items.map(productCardHtml).join('');
                applyFilters();
            })
            .catch(function () { /* keep current grid */ });
    }

    var refreshMenuBtn = document.getElementById('refreshMenuBtn');
    if (refreshMenuBtn) {
        refreshMenuBtn.addEventListener('click', function () {
            refreshCatalog();
            toast('Menu refreshed');
        });
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

    function isTableOrder(type) {
        return type === 'dinein' || type === 'qr' || type === 'walkin';
    }

    var lastTableId = document.getElementById('tableIdInput').value || '';
    var lastTableCode = '';

    function resetToNewOrder() {
        document.getElementById('resumeOrderId').value = '';
        document.getElementById('orderNumberLabel').textContent = '#' + (config.nextOrderNumber || '');
        lastKitchenMap = {};
    }

    function bumpNextOrderNumber(usedNumber) {
        var source = String(usedNumber || config.nextOrderNumber || '').replace(/^#/, '').trim();
        var m = source.match(/^(ORD-\d+-)?(\d+)$/i);
        if (m) {
            var prefix = m[1] || '';
            config.nextOrderNumber = prefix + String(parseInt(m[2], 10) + 1).padStart(m[2].length, '0');
            return;
        }
        var tail = source.match(/^(.*?)(\d+)$/);
        if (!tail) return;
        config.nextOrderNumber = tail[1] + String(parseInt(tail[2], 10) + 1).padStart(tail[2].length, '0');
    }

    function applyNextOrderNumber(serverNext, usedNumber) {
        if (serverNext) {
            config.nextOrderNumber = String(serverNext).replace(/^#/, '').trim();
        } else {
            bumpNextOrderNumber(usedNumber);
        }
    }

    function startFreshTicket() {
        cart = [];
        discount = 0;
        discountPct = null;
        applyService = config.applyServiceDefault === true;
        applyTax = config.applyTaxDefault === true;
        serviceRatePct = defaultServiceRate;
        vatRatePct = defaultVatRate;
        if (applyServiceToggle) applyServiceToggle.checked = applyService;
        if (applyTaxToggle) applyTaxToggle.checked = applyTax;
        document.getElementById('resumeOrderId').value = '';
        resetToNewOrder();
        document.querySelectorAll('#discountPresets button').forEach(function (b) { b.classList.remove('active'); });
        syncCustomer('', '', '');
        syncTable('', '', { skipLookup: true });
        render();
    }

    function loadOrderPayload(order, message) {
        if (!order) return;
        cart = (order.items || []).map(function (item) {
            return {
                id: String(item.menu_item_id || ''),
                name: item.name,
                price: Number(item.price),
                qty: Number(item.qty),
                note: item.note || '',
            };
        });
        discount = Number(order.discount_amount || 0);
        discountPct = null;
        applyService = order.apply_service === true || Number(order.service_charge || 0) > 0;
        applyTax = order.apply_tax === true || Number(order.tax_amount || 0) > 0;
        if (applyServiceToggle) applyServiceToggle.checked = applyService;
        if (applyTaxToggle) applyTaxToggle.checked = applyTax;
        lastKitchenMap = kitchenMapFromCart(cart);
        document.getElementById('resumeOrderId').value = order.id;
        document.getElementById('orderNumberLabel').textContent = '#' + order.order_number;
        document.getElementById('promoCode').value = order.promo_code || '';
        document.getElementById('orderNotes').value = order.notes || '';
        setType(order.type === 'walkin' || order.type === 'qr' ? 'dinein' : order.type);
        if (order.table_id && order.table_code) {
            syncTable(order.table_id, order.table_code, { skipLookup: true });
        } else {
            syncTable('', '', { skipLookup: true });
        }
        syncCustomer(order.customer_id || '', order.customer_name || '', order.customer_phone || '');
        render();
        toast(message || 'Order loaded');
    }

    function lookupTableOrder(tableId, tableCode) {
        if (!config.tableOrderUrl || !tableId) return Promise.resolve();
        return fetch(config.tableOrderUrl + '?table_id=' + encodeURIComponent(tableId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var resumeId = document.getElementById('resumeOrderId').value;
                if (!data.order) {
                    if (resumeId) {
                        if (cart.length && !confirm('No open order on Table ' + tableCode + '. Start a new order?')) {
                            return;
                        }
                        cart = [];
                        discount = 0;
                        discountPct = null;
                        resetToNewOrder();
                        render();
                    }
                    return;
                }
                if (String(data.order.id) === String(resumeId)) return;
                var itemCount = (data.order.items || []).reduce(function (s, i) { return s + Number(i.qty || 0); }, 0);
                if (cart.length) {
                    var msg = 'Table ' + tableCode + ' has open order ' + data.order.order_number + ' (' + itemCount + ' items). Replace current cart?';
                    if (!confirm(msg)) return;
                }
                loadOrderPayload(data.order, 'Open order loaded · Table ' + tableCode);
            })
            .catch(function () {
                toast('Could not check table order');
            });
    }

    function isPayFirst() {
        return config.payFirst === true;
    }

    function isDineInType(type) {
        type = type || (document.getElementById('orderType') && document.getElementById('orderType').value) || 'dinein';
        return type === 'dinein' || type === 'qr' || type === 'walkin';
    }

    function hasOpenTicket() {
        var resume = document.getElementById('resumeOrderId');
        return !!(resume && resume.value);
    }

    function canDirectPay() {
        if (isPayFirst()) return true;
        return !isDineInType();
    }

    function canPayNow() {
        if (isPayFirst()) return true;
        if (!isDineInType()) return true;
        return hasOpenTicket();
    }

    function syncCheckoutMode() {
        var payBtn = document.getElementById('payBtn');
        var holdBtn = document.getElementById('holdBtn');
        var saveBtn = document.getElementById('saveBtn');
        var payAllowed = canPayNow();
        if (payBtn) {
            payBtn.classList.toggle('is-locked', !payAllowed);
            payBtn.title = payAllowed
                ? (canDirectPay() ? 'Collect payment now' : 'Collect payment for this sent order')
                : 'Dine-in: send the order first, then pay';
        }
        var sendAllowed = !isPayFirst();
        if (holdBtn) holdBtn.classList.toggle('hidden', !sendAllowed);
        if (saveBtn) saveBtn.classList.toggle('hidden', !sendAllowed);
    }

    function setType(type) {
        document.getElementById('orderType').value = type;
        document.querySelectorAll('#serviceType button').forEach(function (b) {
            b.classList.toggle('active', b.dataset.type === type);
        });

        var label = typeLabel(type);
        var ctxLabel = document.getElementById('ctxTypeLabel');
        if (ctxLabel) ctxLabel.textContent = label;

        var dineinOnly = document.querySelectorAll('.dinein-only');
        var showTable = isTableOrder(type);
        dineinOnly.forEach(function (el) {
            el.classList.toggle('hidden', !showTable);
        });

        var hint = document.getElementById('typeHint');
        var hintText = document.getElementById('typeHintText');
        if (hint) hint.classList.toggle('hidden', showTable);
        if (hintText) {
            hintText.textContent = type === 'delivery'
                ? 'Delivery order · no table'
                : 'Counter pickup · no table';
        }

        if (showTable) {
            if (!document.getElementById('tableIdInput').value && lastTableId) {
                syncTable(lastTableId, lastTableCode || '—', { skipLookup: true });
            }
        } else {
            var currentTable = document.getElementById('tableIdInput').value;
            if (currentTable) {
                lastTableId = currentTable;
                lastTableCode = lastTableCode || '';
            }
            document.getElementById('tableIdInput').value = '';
        }

        var printBtn = document.getElementById('printBtn');
        if (printBtn) {
            var allowDueBill = type !== 'takeaway';
            printBtn.classList.toggle('hidden', !allowDueBill);
            printBtn.disabled = !allowDueBill;
            if (!allowDueBill) closeDueBillPreview();
        }
        syncCheckoutMode();
    }

    document.getElementById('serviceType').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        setType(btn.dataset.type);
    });

    function syncTable(id, code, opts) {
        opts = opts || {};
        id = id || '';
        code = code || '';
        document.getElementById('tableIdInput').value = id;
        var top = document.getElementById('topTableSelect');
        if (top) top.value = id;
        var modalSel = document.getElementById('modalTableSelect');
        if (modalSel) modalSel.value = id;
        lastTableId = id;
        lastTableCode = code;
        if (!opts.skipLookup && id && isTableOrder(document.getElementById('orderType').value)) {
            lookupTableOrder(id, code);
        }
    }

    document.getElementById('topTableSelect').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        syncTable(this.value, opt ? (opt.dataset.code || '') : '');
    });

    var tablePickerBtn = document.getElementById('tablePickerBtn');
    if (tablePickerBtn) {
        tablePickerBtn.addEventListener('click', function () {
            document.getElementById('tableModal').classList.remove('hidden');
        });
    }    document.getElementById('cancelTableModal').addEventListener('click', function () {
        document.getElementById('tableModal').classList.add('hidden');
    });
    document.getElementById('confirmTableBtn').addEventListener('click', function () {
        var sel = document.getElementById('modalTableSelect');
        var opt = sel.options[sel.selectedIndex];
        syncTable(sel.value, opt ? (opt.dataset.code || '') : '');
        document.getElementById('tableModal').classList.add('hidden');
    });

    function syncCustomer(id, name, phone) {
        document.getElementById('customerSelect').value = id || '';
        document.getElementById('customerName').value = name || '';
        var phoneInput = document.getElementById('customerPhone');
        if (phoneInput) phoneInput.value = phone || '';
        var guestName = document.getElementById('guestNameField');
        var guestPhone = document.getElementById('guestPhoneField');
        if (guestName) guestName.value = name || '';
        if (guestPhone) guestPhone.value = phone || '';
        var modalName = document.getElementById('modalCustomerName');
        var modalPhone = document.getElementById('modalCustomerPhone');
        if (modalName) modalName.value = name || '';
        if (modalPhone) modalPhone.value = phone || '';
    }

    function pullGuestFields() {
        var guestName = document.getElementById('guestNameField');
        var guestPhone = document.getElementById('guestPhoneField');
        var name = guestName ? (guestName.value || '').trim() : '';
        var phone = guestPhone ? (guestPhone.value || '').trim() : '';
        document.getElementById('customerName').value = name;
        document.getElementById('customerPhone').value = phone;
        var modalName = document.getElementById('modalCustomerName');
        var modalPhone = document.getElementById('modalCustomerPhone');
        if (modalName) modalName.value = name;
        if (modalPhone) modalPhone.value = phone;
    }

    var guestNameField = document.getElementById('guestNameField');
    var guestPhoneField = document.getElementById('guestPhoneField');
    if (guestNameField) {
        guestNameField.addEventListener('input', function () {
            // Typing a walk-in name clears linked CRM customer id
            document.getElementById('customerSelect').value = '';
            pullGuestFields();
        });
    }
    if (guestPhoneField) {
        guestPhoneField.addEventListener('input', pullGuestFields);
    }

    var customerPickerBtn = document.getElementById('customerPickerBtn');
    if (customerPickerBtn) {
        customerPickerBtn.addEventListener('click', function () {
            document.getElementById('modalCustomerSelect').value = document.getElementById('customerSelect').value;
            document.getElementById('customerModal').classList.remove('hidden');
        });
    }
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
    var holdCountChip = document.getElementById('holdCountChip');
    if (holdCountChip) {
        holdCountChip.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('notifPanel').classList.toggle('hidden');
        });
    }

    var payFirstToggle = document.getElementById('payFirstToggle');
    function syncPayFirstChip() {
        if (!payFirstToggle) return;
        var on = isPayFirst();
        payFirstToggle.classList.toggle('is-on', on);
        payFirstToggle.setAttribute('aria-pressed', on ? 'true' : 'false');
        var state = document.getElementById('payFirstState');
        if (state) state.textContent = on ? 'ON' : 'OFF';
        syncCheckoutMode();
    }
    if (payFirstToggle) {
        payFirstToggle.addEventListener('click', function () {
            var next = !isPayFirst();
            var url = config.payFirstUrl;
            if (!url) {
                config.payFirst = next;
                syncPayFirstChip();
                toast(next ? 'Pay-first on' : 'Pay-first off');
                return;
            }
            var body = new FormData();
            body.set('pay_first', next ? '1' : '0');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: body,
                credentials: 'same-origin',
            }).then(function (res) { return res.json(); }).then(function (data) {
                config.payFirst = !!(data.pay_first);
                syncPayFirstChip();
                toast(data.message || (config.payFirst ? 'Pay-first on' : 'Pay-first off'));
            }).catch(function () {
                toast('Could not save pay-first setting');
            });
        });
        syncPayFirstChip();
    }
    document.addEventListener('click', function () {
        document.getElementById('notifPanel').classList.add('hidden');
    });
    document.getElementById('notifPanel').addEventListener('click', function (e) {
        e.stopPropagation();
    });

    var openOrders = config.openOrders || [];
    var notifPanel = document.getElementById('notifPanel');
    if (notifPanel) {
        notifPanel.addEventListener('click', function (e) {
            var heldBtn = e.target.closest('.resume-held');
            if (heldBtn) {
                var held = heldOrders.find(function (o) { return String(o.id) === String(heldBtn.dataset.id); });
                if (!held) return;
                loadOrderPayload(held, 'Previous order loaded · add items then Send');
                notifPanel.classList.add('hidden');
                return;
            }
            var openBtn = e.target.closest('.resume-open');
            if (openBtn) {
                var order = openOrders.find(function (o) { return String(o.id) === String(openBtn.dataset.id); });
                if (!order) return;
                if (cart.length) {
                    var ok = confirm('Load Token ' + (order.token_number || order.order_number) + ' and replace current cart?');
                    if (!ok) return;
                }
                loadOrderPayload(order, 'Open order loaded · Token ' + (order.token_number || ''));
                notifPanel.classList.add('hidden');
            }
        });
    }

    function syncHeldBadge() {
        var n = heldOrders.length;
        var badge = document.getElementById('heldCountBadge');
        var label = document.getElementById('heldCountLabel');
        if (badge) {
            badge.textContent = String(n);
            badge.classList.toggle('hidden', n < 1);
        }
        var heldText = document.getElementById('heldCountText');
        if (heldText) heldText.textContent = String(n);
        if (label) label.textContent = String(n);
    }

    function normalizeOrderQuery(q) {
        return String(q || '').replace(/^#/, '').trim().toLowerCase();
    }

    function orderMatchesQuery(order, q) {
        if (!order || !q) return false;
        if (String(order.id) === q) return true;
        var num = String(order.order_number || '').toLowerCase();
        if (num === q || num.indexOf(q) !== -1) return true;
        if (String(order.token_number || '').toLowerCase() === q) return true;
        return false;
    }

    function findLocalOrder(q) {
        q = normalizeOrderQuery(q);
        var fromHeld = heldOrders.find(function (o) { return orderMatchesQuery(o, q); });
        if (fromHeld) return fromHeld;
        return (openOrders || []).find(function (o) { return orderMatchesQuery(o, q); }) || null;
    }

    function recallOrder(raw) {
        var q = String(raw || '').trim();
        if (!q) {
            toast('Enter an order number or ID');
            return;
        }
        var local = findLocalOrder(q);
        if (local) {
            loadOrderPayload(local, 'Loaded ' + (local.order_number || ('#' + local.id)));
            document.getElementById('notifPanel').classList.add('hidden');
            return;
        }
        if (!config.findOrderUrl) {
            toast('Order not found');
            return;
        }
        fetch(config.findOrderUrl + '?q=' + encodeURIComponent(normalizeOrderQuery(q)), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.order) {
                    toast('No unpaid order matches ' + q);
                    return;
                }
                upsertHeldOrder(data.order);
                loadOrderPayload(data.order, 'Loaded ' + data.order.order_number);
                document.getElementById('notifPanel').classList.add('hidden');
            })
            .catch(function () { toast('Could not find order'); });
    }

    var recallBtn = document.getElementById('recallOrderBtn');
    var recallInput = document.getElementById('recallOrderInput');
    if (recallBtn && recallInput) {
        recallBtn.addEventListener('click', function () { recallOrder(recallInput.value); });
        recallInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recallOrder(recallInput.value);
            }
        });
    }
    var heldFindInput = document.getElementById('heldFindInput');
    if (heldFindInput) {
        heldFindInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recallOrder(heldFindInput.value);
            }
        });
    }

    function renderHeldList() {
        var wrap = document.getElementById('heldOrdersWrap');
        if (wrap) {
            if (!heldOrders.length) {
                wrap.innerHTML = '<p class="notif-empty">No held orders right now.</p>';
            } else {
                wrap.innerHTML = '<ul id="heldOrdersList">' + heldOrders.map(function (o) {
                    var count = (o.items || []).reduce(function (s, i) { return s + Number(i.qty || 1); }, 0);
                    return '<li><button type="button" class="resume-held" data-id="' + o.id + '">'
                        + '<strong>' + escapeHtml(o.order_number || '') + '</strong>'
                        + '<small>' + escapeHtml(String(o.type || '')) + ' · ' + count + ' items · ' + money(o.total) + '</small>'
                        + '</button></li>';
                }).join('') + '</ul>';
            }
        }
        syncHeldBadge();
    }

    function upsertHeldOrder(order) {
        if (!order || !order.id) return;
        heldOrders = [order].concat(heldOrders.filter(function (o) {
            return String(o.id) !== String(order.id);
        }));
        renderHeldList();
    }

    function dropHeldOrder(id) {
        if (!id) return;
        heldOrders = heldOrders.filter(function (o) { return String(o.id) !== String(id); });
        renderHeldList();
    }

    function round2(n) {
        return Math.round((Number(n) || 0) * 100) / 100;
    }

    var payTenderedTouched = false;

    function readPayAmounts() {
        return {
            cash: round2(document.getElementById('payCash').value),
            bkash: round2(document.getElementById('payBkash').value),
            card: round2(document.getElementById('payCard').value),
            tendered: round2(document.getElementById('payTendered').value),
        };
    }

    function syncPaySummary() {
        var due = round2(totals().total);
        var bkash = round2(document.getElementById('payBkash').value);
        var card = round2(document.getElementById('payCard').value);
        var digital = round2(bkash + card);
        var digitalOver = digital > due + 0.001;
        var cash = digitalOver ? 0 : round2(Math.max(0, due - digital));
        var cashEl = document.getElementById('payCash');
        cashEl.value = cash > 0 ? cash.toFixed(2) : '0.00';

        var tenderedEl = document.getElementById('payTendered');
        if (!payTenderedTouched) {
            tenderedEl.value = cash > 0 ? cash.toFixed(2) : '';
        }
        var tendered = round2(tenderedEl.value);

        // Change only when bill is fully covered and cash was collected
        var allocated = round2(cash + bkash + card);
        var remaining = round2(due - allocated);
        var balanced = !digitalOver && Math.abs(remaining) < 0.05;
        var change = (balanced && cash > 0) ? Math.max(0, round2(tendered - cash)) : 0;

        document.getElementById('payDueAmount').textContent = money(due);
        document.getElementById('payAllocated').textContent = money(allocated);
        document.getElementById('payRemaining').textContent = digitalOver
            ? money(round2(digital - due)) + ' over'
            : money(remaining);
        document.getElementById('payChange').textContent = money(change);

        var remEl = document.getElementById('payRemaining');
        remEl.classList.toggle('ok', balanced);
        remEl.classList.toggle('bad', !balanced);

        var tenderWrap = tenderedEl.closest('label');
        if (tenderWrap) tenderWrap.classList.toggle('dimmed', cash <= 0);
        tenderedEl.disabled = cash <= 0;
        if (cash <= 0) {
            tenderedEl.value = '';
            payTenderedTouched = false;
        }

        var methods = [];
        if (cash > 0) methods.push('cash');
        if (bkash > 0) methods.push('bkash');
        if (card > 0) methods.push('card');
        document.getElementById('paymentMethod').value = methods.length > 1 ? 'split' : (methods[0] || 'cash');
        document.getElementById('cashPaidInput').value = cash;
        document.getElementById('bkashPaidInput').value = bkash;
        document.getElementById('cardPaidInput').value = card;
        document.getElementById('amountTenderedInput').value = cash > 0 ? tendered : 0;
        document.getElementById('changeAmountInput').value = change;

        return {
            due: due,
            allocated: allocated,
            remaining: remaining,
            change: change,
            digitalOver: digitalOver,
            balanced: balanced,
            amounts: { cash: cash, bkash: bkash, card: card, tendered: tendered },
        };
    }

    function fillPayMethod(method) {
        var due = round2(totals().total);
        payTenderedTouched = false;
        document.getElementById('payBkash').value = method === 'bkash' ? due.toFixed(2) : '';
        document.getElementById('payCard').value = method === 'card' ? due.toFixed(2) : '';
        if (method === 'cash') {
            document.getElementById('payTendered').value = due.toFixed(2);
        } else {
            document.getElementById('payTendered').value = '';
        }
        document.querySelectorAll('.pay-quick button').forEach(function (b) {
            b.classList.toggle('active', b.dataset.fill === method);
        });
        syncPaySummary();
    }

    document.querySelectorAll('.pay-quick button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            fillPayMethod(btn.dataset.fill);
        });
    });
    ['payBkash', 'payCard'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () {
            payTenderedTouched = false;
            syncPaySummary();
        });
    });
    var payTenderedEl = document.getElementById('payTendered');
    if (payTenderedEl) {
        payTenderedEl.addEventListener('input', function () {
            payTenderedTouched = true;
            syncPaySummary();
        });
    }

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
        if (!canPayNow()) {
            toast('Dine-in cannot be paid directly. Send first, then Pay.');
            return;
        }
        pullGuestFields();
        var nameEl = document.getElementById('modalCustomerName');
        var phoneEl = document.getElementById('modalCustomerPhone');
        if (nameEl) nameEl.value = document.getElementById('customerName').value || '';
        if (phoneEl) phoneEl.value = document.getElementById('customerPhone').value || '';
        payTenderedTouched = false;
        fillPayMethod('cash');
        var err = document.getElementById('payError');
        if (err) { err.classList.add('hidden'); err.textContent = ''; }
        document.getElementById('payModal').classList.remove('hidden');
        syncPaySummary();
    });
    document.getElementById('cancelPayModal').addEventListener('click', function () {
        document.getElementById('payModal').classList.add('hidden');
    });
    var cancelPayModalBtn = document.getElementById('cancelPayModalBtn');
    if (cancelPayModalBtn) {
        cancelPayModalBtn.addEventListener('click', function () {
            document.getElementById('payModal').classList.add('hidden');
        });
    }
    document.getElementById('confirmPayBtn').addEventListener('click', function () {
        var summary = syncPaySummary();
        var err = document.getElementById('payError');
        if (summary.digitalOver) {
            err.textContent = 'bKash + Card cannot exceed amount due.';
            err.classList.remove('hidden');
            return;
        }
        if (!summary.balanced) {
            err.textContent = 'Cash + bKash + Card must equal amount due.';
            err.classList.remove('hidden');
            return;
        }
        if (summary.amounts.cash > 0 && summary.amounts.tendered + 0.001 < summary.amounts.cash) {
            err.textContent = 'Cash received must be at least the cash toward bill.';
            err.classList.remove('hidden');
            return;
        }
        err.classList.add('hidden');
        var name = (document.getElementById('modalCustomerName').value || '').trim();
        var phone = (document.getElementById('modalCustomerPhone').value || '').trim();
        syncCustomer(document.getElementById('customerSelect').value || '', name, phone);
        document.getElementById('orderNotes').value = document.getElementById('modalNotes').value;
        document.getElementById('posAction').value = 'pay';
        document.getElementById('payModal').classList.add('hidden');
        submitTicketAjax('pay');
    });

    document.getElementById('posForm').addEventListener('submit', function (e) {
        e.preventDefault();
        pullGuestFields();
        if (!cart.length) {
            toast('Add at least one menu item');
            return;
        }
        if (e.submitter && e.submitter.dataset.action) {
            document.getElementById('posAction').value = e.submitter.dataset.action;
        }
        var action = document.getElementById('posAction').value || 'save';
        if ((action === 'save' || action === 'hold') && isPayFirst()) {
            toast('Pay-first restaurant: collect payment before sending.');
            return;
        }
        submitTicketAjax(action);
    });

    function kitchenLineKey(item) {
        return String(item.id) + '::' + (item.note || '');
    }

    function kitchenMapFromCart(list) {
        var map = {};
        (list || []).forEach(function (item) {
            var key = kitchenLineKey(item);
            map[key] = (map[key] || 0) + Number(item.qty);
        });
        return map;
    }

    function tokensFromCart(isAddition) {
        var orderNo = (document.getElementById('orderNumberLabel').textContent || '').replace(/^#\s*/, '').trim();
        var tokenMatch = orderNo.match(/(\d+)$/);
        var type = document.getElementById('orderType').value || 'dinein';
        var tableText = currentTableLabel();
        var tableCode = tableText.replace(/^Table\s+/i, '').trim();
        if (tableCode === '—' || tableCode === '-' || tableCode === 'No seat') tableCode = '';
        var customerItems = cart.map(function (item) {
            return { name: item.name + (item.note ? ' (' + item.note + ')' : ''), qty: item.qty };
        });
        var kitchenItems = [];
        var hadPrevious = Object.keys(lastKitchenMap).length > 0;
        cart.forEach(function (item) {
            var key = kitchenLineKey(item);
            var prev = lastKitchenMap[key] || 0;
            var add = item.qty - prev;
            if (add > 0) {
                kitchenItems.push({
                    name: item.name + (item.note ? ' (' + item.note + ')' : ''),
                    qty: add,
                });
            }
        });
        if (!hadPrevious) kitchenItems = customerItems.slice();
        var t = totals();
        return {
            order_number: orderNo,
            token_number: tokenMatch ? tokenMatch[1] : orderNo,
            is_addition: !!(isAddition && hadPrevious),
            type_label: typeLabel(type),
            table: tableCode || null,
            seat_label: tableCode ? ('Table ' + tableCode) : 'No seat yet',
            customer_name: currentCustomerLabel(),
            cashier: config.cashierName || 'Cashier',
            placed_at: new Date().toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }),
            notes: (document.getElementById('orderNotes').value || '').trim(),
            customer_items: customerItems,
            kitchen_items: kitchenItems,
            item_count: itemCount(),
            subtotal: t.subtotal,
            total: t.total,
        };
    }

    function csrfToken() {
        var input = document.querySelector('#posForm input[name="_token"]');
        return input ? input.value : '';
    }

    function numField(id) {
        var el = document.getElementById(id);
        return el ? Number(el.value || 0) : 0;
    }

    function invoiceFromCart() {
        var t = totals();
        var orderNo = (document.getElementById('orderNumberLabel').textContent || '').replace(/^#\s*/, '').trim();
        var type = document.getElementById('orderType').value || 'dinein';
        var tableText = currentTableLabel();
        var tableCode = tableText.replace(/^Table\s+/i, '').trim();
        if (tableCode === '—' || tableCode === '-' || tableCode === 'No seat') tableCode = '';
        var now = new Date().toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        return {
            order_number: orderNo,
            type: type,
            type_label: typeLabel(type),
            customer_name: currentCustomerLabel(),
            customer_phone: (document.getElementById('customerPhone').value || '').trim(),
            table: tableCode || null,
            cashier: config.cashierName || 'Cashier',
            placed_at: now,
            items: cart.map(function (item) {
                return {
                    name: item.name + (item.note ? ' (' + item.note + ')' : ''),
                    qty: item.qty,
                    unit_price: item.price,
                    line_total: item.price * item.qty,
                };
            }),
            subtotal: t.subtotal,
            service_charge: t.service,
            tax_amount: t.tax,
            discount_amount: discount,
            total: t.total,
            cash_paid: numField('cashPaidInput'),
            bkash_paid: numField('bkashPaidInput'),
            card_paid: numField('cardPaidInput'),
            amount_tendered: numField('amountTenderedInput'),
            change_amount: numField('changeAmountInput'),
            notes: (document.getElementById('orderNotes').value || '').trim(),
        };
    }

    function submitTicketAjax(action) {
        if (sendingOrder) return;
        sendingOrder = true;
        render();
        var resumeId = document.getElementById('resumeOrderId').value;
        var form = document.getElementById('posForm');
        document.getElementById('posAction').value = action;
        var body = new FormData(form);
        body.set('action', action);

        var storeUrl = form.getAttribute('action') || '/admin/pos';
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: body,
            credentials: 'same-origin',
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) {
                    var first = data.message || (data.errors && Object.values(data.errors)[0] && Object.values(data.errors)[0][0]) || 'Could not save order';
                    throw new Error(first);
                }
                return data;
            });
        }).then(function (data) {
            if (action === 'pay') {
                dropHeldOrder(data.order && data.order.id);
                applyNextOrderNumber(data.next_order_number, data.order && data.order.order_number);
                startFreshTicket();
                toast(data.message || 'Payment captured');
                return;
            }
            if (action === 'save' || action === 'hold') {
                upsertHeldOrder(data.order);
                if (action === 'save') {
                    applyNextOrderNumber(data.next_order_number, data.order && data.order.order_number);
                    startFreshTicket();
                    toast(data.message || 'Sent · POS ready for next order');
                    return;
                }
            }
            if (data.order) {
                document.getElementById('resumeOrderId').value = data.order.id;
                document.getElementById('orderNumberLabel').textContent = '#' + data.order.order_number;
            }
            lastKitchenMap = kitchenMapFromCart(cart);
            toast(data.message || 'Order held');
        }).catch(function (err) {
            toast(err.message || 'Could not save. Try again.');
        }).finally(function () {
            sendingOrder = false;
        });

        if (action === 'pay') {
            if (isPayFirst()) window._printTokensAfterInvoice = tokensFromCart(!!resumeId);
            printPaidInvoice(invoiceFromCart());
        } else if (action === 'save') {
            printTokens(tokensFromCart(!!resumeId));
        }
    }

    document.getElementById('copyOrderBtn').addEventListener('click', function () {
        var text = (document.getElementById('orderNumberLabel').textContent || '').replace(/^#/, '').trim();
        if (navigator.clipboard) navigator.clipboard.writeText(text);
        toast('Order number copied');
    });

    document.getElementById('printBtn').addEventListener('click', function () {
        openDueBillPreview();
    });

    var dueBillPreview = document.getElementById('dueBillPreview');
    var dueBillShell = document.querySelector('.pos-shell');

    function openDueBillPreview() {
        var type = document.getElementById('orderType').value || 'dinein';
        if (type === 'takeaway') {
            toast('Due bill is only for dine-in / delivery');
            return;
        }
        if (!cart.length) { toast('Add items before printing due bill'); return; }
        fillDueBill();
        openSlipPreview(document.getElementById('dueBillSheet'), 'Due bill');
    }

    function openSlipPreview(node, title) {
        if (!node) return;
        var preview = document.getElementById('slipPreview');
        var stage = document.getElementById('slipPreviewStage');
        var titleEl = document.getElementById('slipPreviewTitle');
        if (!preview || !stage) {
            printIsolated(node);
            return;
        }
        window._slipPreviewNode = node;
        if (titleEl) titleEl.textContent = title || 'Slip';
        stage.innerHTML = '';
        var clone = node.cloneNode(true);
        clone.removeAttribute('id');
        clone.classList.remove('hidden');
        clone.style.display = 'block';
        Array.prototype.forEach.call(clone.querySelectorAll('[id]'), function (el) {
            el.removeAttribute('id');
        });
        stage.appendChild(clone);
        preview.classList.remove('hidden');
        if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
        if (typeof window.__posRestoreExtended === 'function') window.__posRestoreExtended();
    }

    function closeSlipPreview() {
        var preview = document.getElementById('slipPreview');
        if (preview) preview.classList.add('hidden');
        window._slipPreviewNode = null;
    }

    function isSlipPreviewOpen() {
        var preview = document.getElementById('slipPreview');
        return preview && !preview.classList.contains('hidden');
    }

    function confirmSlipPrint() {
        if (!isSlipPreviewOpen()) return;
        var node = window._slipPreviewNode;
        var followTokens = window._printTokensAfterInvoice;
        window._printTokensAfterInvoice = null;
        closeSlipPreview();
        if (typeof window.__posRestoreExtended === 'function') window.__posRestoreExtended();
        printSequence(slipPrintParts(node), function () {
            if (followTokens) printTokens(followTokens);
        });
    }

    var slipPreviewClose = document.getElementById('slipPreviewClose');
    var slipPreviewPrint = document.getElementById('slipPreviewPrint');
    var slipPreviewBackdrop = document.getElementById('slipPreviewBackdrop');
    if (slipPreviewClose) slipPreviewClose.addEventListener('click', closeSlipPreview);
    if (slipPreviewPrint) slipPreviewPrint.addEventListener('click', confirmSlipPrint);
    if (slipPreviewBackdrop) slipPreviewBackdrop.addEventListener('click', closeSlipPreview);

    function premiumSlipCss() {
        return '<style>'
            + '*{box-sizing:border-box}html,body{margin:0;padding:0;background:#fff;color:#1c1410}'
            + '.token-print-sheet,.paid-invoice-sheet,.due-bill-sheet,.due-bill-sheet--live{display:block!important;position:static!important}'
            + '.hidden{display:none!important}'
            + '.token-slip,.due-bill,.paid-invoice{width:72mm;margin:0 auto;padding:3mm 2mm;font-family:\'Segoe UI\',sans-serif}'
            + '.token-slip{page-break-after:always}.token-slip:last-child{page-break-after:auto}'
            + '.slip-crest{text-align:center;color:#b8860b;font-size:12px;letter-spacing:.45em;margin-bottom:2px}'
            + '.slip-rule{height:3px;margin:8px 0 10px;border-top:1px solid #c4a574;border-bottom:1px solid #e8d5a8}'
            + '.token-brand,.due-bill-brand{text-align:center;font-family:Georgia,\'Times New Roman\',serif;font-size:18px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}'
            + '.token-tagline,.due-bill-tagline{text-align:center;font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:#8a7350;margin-top:2px}'
            + '.due-bill-contact{text-align:center;font-size:10px;color:#5c4a38;margin-top:3px}'
            + '.token-badge,.due-bill-badge{margin:8px auto;text-align:center;font-size:9px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#7a5c2e;border:1px solid #c4a574;padding:5px 10px;width:fit-content}'
            + '.due-bill-badge{display:block;width:auto;border-radius:4px}'
            + '.due-bill-badge.paid{color:#0f7a4a;border-color:#0f7a4a;background:#f0faf4}'
            + '.token-number{text-align:center;font-family:Georgia,serif;font-size:34px;font-weight:700;letter-spacing:.08em;margin:4px 0 8px}'
            + '.token-meta,.due-bill-meta{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;font-size:10px;margin:8px 0;padding:8px 0;border-top:1px dashed #cfc3ae;border-bottom:1px dashed #cfc3ae}'
            + '.token-meta span,.due-bill-meta span{display:block;color:#8a7350;font-size:8px;letter-spacing:.08em;text-transform:uppercase}'
            + '.token-meta strong,.due-bill-meta strong{font-size:11px}'
            + '.token-items{list-style:none;margin:0;padding:0;font-size:12px}'
            + '.token-items li{display:flex;gap:8px;padding:5px 0;border-bottom:1px dotted #e2d6c4}'
            + '.token-items .q{font-weight:800;min-width:28px;color:#7a5c2e}'
            + '.token-items--kitchen li{font-size:13px;font-weight:700}'
            + '.due-bill-items{width:100%;border-collapse:collapse;font-size:11px}'
            + '.due-bill-items th{text-align:left;font-size:8px;letter-spacing:.08em;text-transform:uppercase;color:#8a7350;border-bottom:1px solid #c4a574;padding:4px 0}'
            + '.due-bill-items td{padding:5px 0;border-bottom:1px dotted #e2d6c4;vertical-align:top}'
            + '.due-bill-items .qty{width:28px}.due-bill-items .amt{text-align:right;white-space:nowrap}'
            + '.due-bill-totals{margin-top:8px;font-size:12px}.due-bill-totals>div{display:flex;justify-content:space-between;padding:3px 0}'
            + '.due-total{font-size:14px;font-weight:800;border-top:1px solid #c4a574;margin-top:6px;padding-top:6px}'
            + '.due-bill-status{margin-top:10px;text-align:center;border:1px solid #c4a574;padding:8px;font-size:10px;letter-spacing:.06em}'
            + '.due-bill-status.paid{border-color:#0f7a4a;color:#0f7a4a}'
            + '.inv-pay-breakdown{margin-top:8px;font-size:11px}.inv-pay-breakdown>div{display:flex;justify-content:space-between;padding:2px 0}'
            + '.token-note,.token-foot,.token-notes,.due-bill-foot{margin:8px 0 0;font-size:10px;text-align:center;color:#5c4a38;line-height:1.45}'
            + '.token-notes{text-align:left;font-weight:700}'
            + '.due-bill-foot .tiny,.tiny{font-size:9px;color:#8a7350}'
            + '@page{size:80mm auto;margin:4mm}'
            + '</style>';
    }

    function slipPrintParts(node) {
        if (!node) return [];
        if (node.classList && node.classList.contains('token-print-sheet')) {
            var parts = [];
            var guest = document.getElementById('customerTokenSlip');
            var kit = document.getElementById('kitchenTokenSlip');
            if (guest && !guest.classList.contains('hidden')) parts.push(guest);
            if (kit && !kit.classList.contains('hidden')) parts.push(kit);
            return parts.length ? parts : [node];
        }
        return [node];
    }

    function printSequence(nodes, afterAll) {
        var list = (nodes || []).filter(function (n) { return n && !n.classList.contains('hidden'); });
        var i = 0;
        function next() {
            if (i >= list.length) {
                if (typeof afterAll === 'function') afterAll();
                return;
            }
            var current = list[i];
            i += 1;
            printIsolated(current, next);
        }
        next();
    }

    function printIsolated(node, onDone) {
        if (!node) {
            if (typeof onDone === 'function') onDone();
            return;
        }
        var iframe = document.getElementById('posSilentPrint');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'posSilentPrint';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.setAttribute('tabindex', '-1');
            iframe.style.cssText = 'position:fixed;left:-10000px;top:0;width:80mm;height:10px;opacity:0;border:0;pointer-events:none;';
            document.body.appendChild(iframe);
        }
        var doc = iframe.contentDocument;
        if (!doc) {
            if (typeof onDone === 'function') onDone();
            return;
        }
        doc.open();
        doc.write('<!DOCTYPE html><html><head><meta charset="utf-8">' + premiumSlipCss() + '</head><body>' + node.outerHTML + '</body></html>');
        doc.close();
        var win = iframe.contentWindow;
        if (!win) {
            if (typeof onDone === 'function') onDone();
            return;
        }
        var finished = false;
        window.__posPrinting = true;
        if (window.__posWantExtended && typeof window.__posApplyExtended === 'function') {
            window.__posApplyExtended(true);
        }
        function done() {
            if (finished) return;
            finished = true;
            window.__posPrinting = false;
            if (typeof window.__posRestoreExtended === 'function') window.__posRestoreExtended();
            if (typeof onDone === 'function') onDone();
        }
        win.onafterprint = done;
        try { win.print(); } catch (err) {}
        if (window.__posWantExtended && typeof window.__posApplyExtended === 'function') {
            window.__posApplyExtended(true);
        }
        done();
    }

    function runDueBillPrint() {
        fillDueBill();
        printIsolated(document.getElementById('dueBillSheet'));
    }

    function fillPaidInvoice(inv) {
        if (!inv) return;
        var restaurant = config.restaurant || {};
        function setText(id, value, allowEmpty) {
            var el = document.getElementById(id);
            if (!el) return;
            if (value == null || value === '') {
                el.textContent = allowEmpty ? '' : '—';
                return;
            }
            el.textContent = value;
        }
        setText('invRestaurantName', restaurant.name || 'Bynnas Restora');
        setText('invTagline', restaurant.tagline || '', true);
        setText('invAddress', restaurant.address || '', true);
        setText('invPhone', [restaurant.phone, restaurant.email].filter(Boolean).join(' · '), true);
        setText('invOrderNo', inv.order_number);
        setText('invDate', inv.placed_at || '');
        setText('invType', inv.type_label || typeLabel(inv.type));
        var tableRow = document.getElementById('invTableRow');
        if (tableRow) tableRow.classList.toggle('hidden', !inv.table);
        setText('invTable', inv.table ? ('Table ' + inv.table) : '—');
        setText('invCustomer', inv.customer_name || 'Walk-in');
        setText('invCustomerPhone', inv.customer_phone || '—');
        setText('invCashier', inv.cashier || config.cashierName || 'Cashier');
        setText('invSubtotal', money(inv.subtotal));
        setText('invTotal', money(inv.total));
        setText('invPrintedAt', 'Printed ' + new Date().toLocaleString());

        var serviceRow = document.getElementById('invServiceRow');
        var taxRow = document.getElementById('invTaxRow');
        var discountRow = document.getElementById('invDiscountRow');
        if (serviceRow) {
            serviceRow.classList.toggle('hidden', !(Number(inv.service_charge) > 0));
            document.getElementById('invService').textContent = money(inv.service_charge);
        }
        if (taxRow) {
            taxRow.classList.toggle('hidden', !(Number(inv.tax_amount) > 0));
            document.getElementById('invTax').textContent = money(inv.tax_amount);
        }
        if (discountRow) {
            discountRow.classList.toggle('hidden', !(Number(inv.discount_amount) > 0));
            document.getElementById('invDiscount').textContent = '-' + money(inv.discount_amount);
        }

        var body = document.getElementById('invItemsBody');
        if (body) {
            body.innerHTML = (inv.items || []).map(function (item) {
                return '<tr>'
                    + '<td class="qty">' + item.qty + '</td>'
                    + '<td><div class="item-name">' + escapeHtml(item.name) + '</div>'
                    + '<div class="item-unit">' + money(item.unit_price) + ' each</div></td>'
                    + '<td class="amt">' + money(item.line_total) + '</td>'
                    + '</tr>';
            }).join('');
        }

        var payBox = document.getElementById('invPayBreakdown');
        if (payBox) {
            var rows = [];
            if (Number(inv.cash_paid) > 0) rows.push('<div><span>Cash</span><strong>' + money(inv.cash_paid) + '</strong></div>');
            if (Number(inv.bkash_paid) > 0) rows.push('<div><span>bKash</span><strong>' + money(inv.bkash_paid) + '</strong></div>');
            if (Number(inv.card_paid) > 0) rows.push('<div><span>Card</span><strong>' + money(inv.card_paid) + '</strong></div>');
            if (Number(inv.amount_tendered) > 0) rows.push('<div><span>Cash received</span><strong>' + money(inv.amount_tendered) + '</strong></div>');
            if (Number(inv.change_amount) > 0) rows.push('<div><span>Change returned</span><strong>' + money(inv.change_amount) + '</strong></div>');
            payBox.innerHTML = rows.join('');
        }
    }

    function printPaidInvoice(inv) {
        fillPaidInvoice(inv);
        openSlipPreview(document.getElementById('paidInvoiceSheet'), 'Paid invoice');
    }

    function fillTokens(tok) {
        if (!tok) return;
        var seat = tok.seat_label || (tok.table ? ('Table ' + tok.table) : 'No seat yet');
        var tokenNo = tok.token_number || '—';

        function setText(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val == null || val === '' ? '—' : val;
        }

        setText('tokCustomerNo', tokenNo);
        setText('tokCustomerOrder', tok.order_number || '—');
        setText('tokCustomerSeat', seat);
        setText('tokCustomerType', tok.type_label || '—');
        setText('tokCustomerTime', tok.placed_at || '—');
        setText('tokCustomerFoot', (tok.customer_name || 'Walk-in') + (tok.cashier ? (' · ' + tok.cashier) : ''));

        var custList = document.getElementById('tokCustomerItems');
        if (custList) {
            custList.innerHTML = (tok.customer_items || []).map(function (i) {
                return '<li><span class="q">' + i.qty + '×</span><span>' + escapeHtml(i.name) + '</span></li>';
            }).join('') || '<li>No items</li>';
        }

        var kitchenItems = tok.kitchen_items || [];
        var kitchenSlip = document.getElementById('kitchenTokenSlip');
        if (kitchenSlip) kitchenSlip.classList.toggle('hidden', !kitchenItems.length);

        setText('tokKitchenNo', tokenNo);
        setText('tokKitchenOrder', tok.order_number || '—');
        setText('tokKitchenSeat', seat);
        setText('tokKitchenType', tok.type_label || '—');
        setText('tokKitchenTime', tok.placed_at || '—');
        var badge = document.getElementById('tokKitchenBadge');
        if (badge) badge.textContent = tok.is_addition ? 'ADD-ON' : 'NEW ORDER';
        var notes = document.getElementById('tokKitchenNotes');
        if (notes) {
            notes.textContent = tok.notes || '';
            notes.classList.toggle('hidden', !tok.notes);
        }
        var kitList = document.getElementById('tokKitchenItems');
        if (kitList) {
            kitList.innerHTML = kitchenItems.map(function (i) {
                return '<li><span class="q">' + i.qty + '×</span><span>' + escapeHtml(i.name) + '</span></li>';
            }).join('');
        }
    }

    function printTokens(tok) {
        if (!tok) return;
        var kitchenOnly = !!tok.is_addition;
        if (kitchenOnly && !(tok.kitchen_items && tok.kitchen_items.length)) {
            toast('No new items for kitchen');
            return;
        }
        fillTokens(tok);
        var customer = document.getElementById('customerTokenSlip');
        if (customer) customer.classList.toggle('hidden', kitchenOnly);
        openSlipPreview(document.getElementById('tokenPrintSheet'), kitchenOnly ? 'Kitchen add-on slip' : 'Guest + kitchen slip');
    }

    var closeDueBillBtn = document.getElementById('closeDueBillPreview');
    var confirmDueBillBtn = document.getElementById('confirmDueBillPrint');
    var dueBillBackdrop = document.getElementById('dueBillBackdrop');
    if (closeDueBillBtn) closeDueBillBtn.addEventListener('click', closeDueBillPreview);
    if (dueBillBackdrop) dueBillBackdrop.addEventListener('click', closeDueBillPreview);
    if (confirmDueBillBtn) confirmDueBillBtn.addEventListener('click', runDueBillPrint);

    function typeLabel(type) {
        return ({ dinein: 'Dine-in', takeaway: 'Takeaway', delivery: 'Delivery', qr: 'QR Order', walkin: 'Walk-in' })[type] || type;
    }

    function fillDueBill() {
        var t = totals();
        var restaurant = config.restaurant || {};
        var currency = restaurant.currency || '৳';
        var orderNo = (document.getElementById('orderNumberLabel').textContent || '').replace(/^#\s*/, '').trim();
        var type = document.getElementById('orderType').value || 'dinein';
        var tableText = currentTableLabel();
        var customer = currentCustomerLabel();
        var now = new Date();
        var notes = (document.getElementById('orderNotes').value || '').trim();

        function setText(id, value, allowEmpty) {
            var el = document.getElementById(id);
            if (!el) return;
            if (value == null || value === '') {
                el.textContent = allowEmpty ? '' : '—';
                return;
            }
            el.textContent = value;
        }

        setText('billRestaurantName', restaurant.name || 'Bynnas Restora');
        setText('billTagline', restaurant.tagline || '', true);
        setText('billAddress', restaurant.address || '', true);
        var phoneBits = [restaurant.phone, restaurant.email].filter(Boolean).join(' · ');
        setText('billPhone', phoneBits, true);
        setText('billOrderNo', orderNo || '—');
        setText('billDate', now.toLocaleString([], {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit'
        }));
        setText('billType', typeLabel(type));
        var tableOrder = isTableOrder(type);
        var billTableRow = document.getElementById('billTableRow');
        var billGuestsRow = document.getElementById('billGuestsRow');
        if (billTableRow) billTableRow.classList.toggle('hidden', !tableOrder);
        if (billGuestsRow) billGuestsRow.classList.add('hidden');
        setText('billTable', tableOrder ? tableText : '—');
        setText('billCustomer', customer || 'Walk-in');
        setText('billCashier', config.cashierName || 'Cashier');
        setText('billSubtotal', money(t.subtotal));
        setText('billTotal', money(t.total));
        setText('billPrintedAt', 'Printed ' + now.toLocaleString());

        var serviceRow = document.getElementById('billServiceRow');
        var taxRow = document.getElementById('billTaxRow');
        var discountRow = document.getElementById('billDiscountRow');
        var notesEl = document.getElementById('billNotes');

        if (serviceRow) {
            serviceRow.classList.toggle('hidden', !(applyService && t.service > 0));
            document.getElementById('billServiceLabel').textContent = 'Service Charge (' + serviceRatePct + '%)';
            document.getElementById('billService').textContent = money(t.service);
        }
        if (taxRow) {
            taxRow.classList.toggle('hidden', !(applyTax && t.tax > 0));
            document.getElementById('billTaxLabel').textContent = taxName + ' (' + vatRatePct + '%)';
            document.getElementById('billTax').textContent = money(t.tax);
        }
        if (discountRow) {
            discountRow.classList.toggle('hidden', !(discount > 0));
            document.getElementById('billDiscount').textContent = '-' + money(discount);
        }
        if (notesEl) {
            if (notes) {
                notesEl.hidden = false;
                notesEl.textContent = 'Note: ' + notes;
            } else {
                notesEl.hidden = true;
                notesEl.textContent = '';
            }
        }

        var body = document.getElementById('billItemsBody');
        if (body) {
            body.innerHTML = cart.map(function (item) {
                var note = item.note ? '<div class="item-note">' + escapeHtml(item.note) + '</div>' : '';
                return '<tr>'
                    + '<td class="qty">' + item.qty + '</td>'
                    + '<td><div class="item-name">' + escapeHtml(item.name) + '</div>' + note
                    + '<div class="item-unit">' + money(item.price) + ' each</div></td>'
                    + '<td class="amt">' + money(item.price * item.qty) + '</td>'
                    + '</tr>';
            }).join('');
        }

        // Keep currency symbol consistent if settings differ
        if (currency && currency !== '৳') {
            ['billSubtotal', 'billService', 'billTax', 'billDiscount', 'billTotal'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.textContent = el.textContent.replace(/^৳/, currency);
            });
        }
    }

    var fsBtn = document.getElementById('fullscreenBtn');
    var fsEnter = document.getElementById('fsEnterIcon');
    var fsExit = document.getElementById('fsExitIcon');
    var shell = document.querySelector('.pos-shell');

    function isNativeFs() {
        return !!(document.fullscreenElement || document.webkitFullscreenElement);
    }

    function isExtended() {
        return typeof window.__posIsExtended === 'function' ? window.__posIsExtended() : isNativeFs();
    }

    function enterExtended() {
        if (typeof window.__posToggleExtended === 'function' && !isExtended()) {
            window.__posToggleExtended();
            return;
        }
    }

    function leaveExtended() {
        if (typeof window.__posLeaveExtended === 'function') {
            window.__posLeaveExtended();
            return;
        }
        var exit = document.exitFullscreen || document.webkitExitFullscreen;
        if (exit && isNativeFs()) {
            try { exit.call(document); } catch (err) {}
        }
        document.documentElement.classList.remove('pos-extended');
        document.body.classList.remove('pos-extended');
        if (shell) shell.classList.remove('pos-extended');
        if (fsEnter) fsEnter.classList.remove('hidden');
        if (fsExit) fsExit.classList.add('hidden');
        if (fsBtn) {
            fsBtn.title = 'Extended form';
            fsBtn.setAttribute('aria-pressed', 'false');
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'F11') {
            e.preventDefault();
            if (typeof window.__posToggleExtended === 'function') window.__posToggleExtended(e);
            return;
        }
        if (e.key === 'Enter' && isSlipPreviewOpen()) {
            e.preventDefault();
            e.stopImmediatePropagation();
            confirmSlipPrint();
            return;
        }
        if (e.key !== 'Escape') return;

        if (isSlipPreviewOpen()) {
            closeSlipPreview();
            e.preventDefault();
            return;
        }

        var notif = document.getElementById('notifPanel');
        if (notif && !notif.classList.contains('hidden')) {
            notif.classList.add('hidden');
            e.preventDefault();
            return;
        }
        if (dueBillPreview && !dueBillPreview.classList.contains('hidden')) {
            closeDueBillPreview();
            e.preventDefault();
            return;
        }
        var payModal = document.getElementById('payModal');
        if (payModal && !payModal.classList.contains('hidden')) {
            payModal.classList.add('hidden');
            e.preventDefault();
            return;
        }
        var cartModal = document.getElementById('cartModal');
        if (cartModal && !cartModal.classList.contains('hidden')) {
            closeCartModal();
            e.preventDefault();
            return;
        }
        var itemModal = document.getElementById('itemModal');
        if (itemModal && !itemModal.classList.contains('hidden')) {
            itemModal.classList.add('hidden');
            e.preventDefault();
            return;
        }
        if (window.__posPrinting) return;
        if (isExtended()) {
            e.preventDefault();
            leaveExtended();
        }
    }, true);

    applyFilters();
    setType(document.getElementById('orderType').value || 'dinein');
    render();
})();
