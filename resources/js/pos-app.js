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

    function cartLineHtml(item, idx) {
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
    }

    function renderCartModal() {
        var list = document.getElementById('cartModalList');
        var countEl = document.getElementById('cartModalCount');
        var subtotalEl = document.getElementById('cartModalSubtotal');
        if (!list) return;

        if (!cart.length) {
            list.innerHTML = '<li class="cart-modal-empty">No items in this order yet.</li>';
        } else {
            list.innerHTML = cart.map(function (item, idx) { return cartLineHtml(item, idx); }).join('');
        }
        if (countEl) {
            var count = itemCount();
            countEl.textContent = count === 1 ? '1 item' : count + ' items';
        }
        if (subtotalEl) subtotalEl.textContent = money(totals().subtotal);
    }

    function openCartModal() {
        if (!cart.length) {
            toast('Add items to view the cart');
            return;
        }
        renderCartModal();
        document.getElementById('cartModal').classList.remove('hidden');
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
        if (btn.classList.contains('note-btn')) {
            openNoteModal(idx);
        }
    }

    function render() {
        var t = totals();
        syncChargeUi();
        if (!cart.length) {
            cartList.innerHTML = '<li class="cart-empty">No items yet. Tap + on a menu item to add.</li>';
        } else {
            cartList.innerHTML = cart.map(function (item, idx) { return cartLineHtml(item, idx); }).join('');
        }

        document.getElementById('subtotalLabel').textContent = money(t.subtotal);
        document.getElementById('serviceLabel').textContent = money(t.service);
        document.getElementById('taxLabel').textContent = money(t.tax);
        document.getElementById('totalLabel').textContent = money(t.total);
        document.getElementById('payAmount').textContent = money(t.total);
        document.getElementById('statItems').textContent = String(itemCount());
        document.getElementById('statTotal').textContent = money(t.total);
        var cartCountLabel = document.getElementById('cartCountLabel');
        if (cartCountLabel) {
            var count = itemCount();
            cartCountLabel.textContent = count === 1 ? '1 item' : count + ' items';
        }
        var expandBtn = document.getElementById('expandCartBtn');
        if (expandBtn) expandBtn.disabled = !cart.length;
        renderCartModal();

        var discountRow = document.getElementById('discountRow');
        var discountLabel = document.getElementById('discountLabel');
        if (discount > 0) {
            discountRow.classList.remove('hidden');
            discountLabel.textContent = '−' + money(discount);
        } else {
            discountRow.classList.add('hidden');
        }

        document.getElementById('serviceChargeInput').value = t.service;
        document.getElementById('taxInput').value = t.tax;
        document.getElementById('discountInput').value = discount;
        var applyServiceInput = document.getElementById('applyServiceInput');
        var applyTaxInput = document.getElementById('applyTaxInput');
        var serviceRateInput = document.getElementById('serviceRateInput');
        var taxRateInput = document.getElementById('taxRateInput');
        if (applyServiceInput) applyServiceInput.value = applyService ? '1' : '0';
        if (applyTaxInput) applyTaxInput.value = applyTax ? '1' : '0';
        if (serviceRateInput) serviceRateInput.value = serviceRatePct;
        if (taxRateInput) taxRateInput.value = vatRatePct;

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

    cartList.addEventListener('click', handleCartLineClick);
    var cartModalList = document.getElementById('cartModalList');
    if (cartModalList) cartModalList.addEventListener('click', handleCartLineClick);

    document.getElementById('expandCartBtn').addEventListener('click', openCartModal);
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
    var lastTableCode = (document.getElementById('statTable').textContent || '').replace(/^Table\s+/i, '').trim();

    function resetToNewOrder() {
        document.getElementById('resumeOrderId').value = '';
        document.getElementById('orderNumberLabel').textContent = '#' + (config.nextOrderNumber || '');
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
                lastTableCode = (document.getElementById('statTable').textContent || '').replace(/^Table\s+/i, '').trim();
            }
            document.getElementById('tableIdInput').value = '';
            document.getElementById('statTable').textContent = '—';
        }

        var printBtn = document.getElementById('printBtn');
        if (printBtn) {
            var allowDueBill = type !== 'takeaway';
            printBtn.classList.toggle('hidden', !allowDueBill);
            printBtn.disabled = !allowDueBill;
            if (!allowDueBill) closeDueBillPreview();
        }
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
        document.getElementById('statTable').textContent = id ? ('Table ' + code) : 'No seat';
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
        document.getElementById('statCustomer').textContent = name || 'Walk-in';
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
        document.getElementById('statCustomer').textContent = name || 'Walk-in';
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

    var openOrders = config.openOrders || [];
    document.querySelectorAll('.resume-held').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var order = heldOrders.find(function (o) { return String(o.id) === String(btn.dataset.id); });
            if (!order) return;
            loadOrderPayload(order, 'Held order loaded');
            document.getElementById('notifPanel').classList.add('hidden');
        });
    });
    document.querySelectorAll('.resume-open').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var order = openOrders.find(function (o) { return String(o.id) === String(btn.dataset.id); });
            if (!order) return;
            if (cart.length) {
                var ok = confirm('Load Token ' + (order.token_number || order.order_number) + ' and replace current cart?');
                if (!ok) return;
            }
            loadOrderPayload(order, 'Open order loaded · Token ' + (order.token_number || ''));
            document.getElementById('notifPanel').classList.add('hidden');
        });
    });

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
        document.getElementById('posForm').requestSubmit();
    });

    document.getElementById('posForm').addEventListener('submit', function (e) {
        pullGuestFields();
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
        if (dueBillPreview) dueBillPreview.classList.remove('hidden');
    }

    function closeDueBillPreview() {
        if (dueBillPreview) dueBillPreview.classList.add('hidden');
    }

    function cleanupDueBillPrint() {
        if (dueBillShell) {
            dueBillShell.classList.remove('printing-due-bill');
            dueBillShell.classList.remove('printing-invoice');
            dueBillShell.classList.remove('printing-tokens');
        }
        document.body.classList.remove('printing-due-bill');
        document.body.classList.remove('printing-invoice');
        document.body.classList.remove('printing-tokens');
    }

    function runDueBillPrint() {
        fillDueBill();
        if (dueBillShell) dueBillShell.classList.add('printing-due-bill');
        document.body.classList.add('printing-due-bill');
        window.print();
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
        if (dueBillShell) dueBillShell.classList.add('printing-invoice');
        document.body.classList.add('printing-invoice');
        setTimeout(function () { window.print(); }, 250);
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
        fillTokens(tok);
        if (dueBillShell) dueBillShell.classList.add('printing-tokens');
        document.body.classList.add('printing-tokens');
        setTimeout(function () { window.print(); }, 250);
    }

    window.addEventListener('afterprint', cleanupDueBillPrint);

    var closeDueBillBtn = document.getElementById('closeDueBillPreview');
    var confirmDueBillBtn = document.getElementById('confirmDueBillPrint');
    var dueBillBackdrop = document.getElementById('dueBillBackdrop');
    if (closeDueBillBtn) closeDueBillBtn.addEventListener('click', closeDueBillPreview);
    if (dueBillBackdrop) dueBillBackdrop.addEventListener('click', closeDueBillPreview);
    if (confirmDueBillBtn) confirmDueBillBtn.addEventListener('click', runDueBillPrint);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && dueBillPreview && !dueBillPreview.classList.contains('hidden')) {
            closeDueBillPreview();
        }
    });

    function typeLabel(type) {
        return ({ dinein: 'Dine-in', takeaway: 'Takeaway', delivery: 'Delivery', qr: 'QR Order', walkin: 'Walk-in' })[type] || type;
    }

    function fillDueBill() {
        var t = totals();
        var restaurant = config.restaurant || {};
        var currency = restaurant.currency || '৳';
        var orderNo = (document.getElementById('orderNumberLabel').textContent || '').replace(/^#\s*/, '').trim();
        var type = document.getElementById('orderType').value || 'dinein';
        var tableText = (document.getElementById('statTable').textContent || '—').trim();
        var customer = (document.getElementById('statCustomer').textContent || 'Walk-in').trim();
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
    setType(document.getElementById('orderType').value || 'dinein');
    render();

    var initialTableId = document.getElementById('tableIdInput').value;
    if (initialTableId && isTableOrder(document.getElementById('orderType').value) && !document.getElementById('resumeOrderId').value && !cart.length) {
        lookupTableOrder(initialTableId, lastTableCode || '—');
    }

    if (config.tokens) {
        printTokens(config.tokens);
    } else if (config.invoice) {
        printPaidInvoice(config.invoice);
    }
})();
