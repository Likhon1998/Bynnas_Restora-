@extends('admin.layouts.pos')

@section('content')
@php
    $type = in_array($defaultType, ['dinein','takeaway','delivery','qr','walkin'], true) ? $defaultType : 'dinein';
    if (in_array($type, ['walkin', 'qr'], true)) {
        $type = 'dinein';
    }
    $defaultTable = $tables->firstWhere('code', '05') ?? $tables->first();
    $icons = [
        'All Items' => '🍽️',
        'Mains' => '🍲',
        'Main Course' => '🍲',
        'Appetizers' => '🥗',
        'Starters' => '🥗',
        'Beverages' => '🥤',
        'Drinks' => '🥤',
        'Desserts' => '🍰',
        'Dessert' => '🍰',
        'Pizza' => '🍕',
        'Burger' => '🍔',
        'Burgers' => '🍔',
        'Combo' => '🍱',
    ];
    $tabCats = collect(['All Items'])->merge($categories)->unique()->values();
    $roleLabel = 'Cashier';
@endphp

@if (session('success'))
    <div class="pos-flash" id="posFlash">{{ session('success') }}</div>
@endif

<div class="pos-shell">
    <header class="pos-topbar">
        <div class="pos-logo">
            <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 13h12M8 13V8a4 4 0 0 1 8 0v5"/>
                    <path d="M4 13h16v2a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-2z"/>
                </svg>
            </div>
            <div class="titles">
                <strong>Bynnas Restora</strong>
                <small>POS System</small>
            </div>
        </div>

        <div class="pos-context">
            <button type="button" class="ctx-btn active" id="ctxDineBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M7 8V5M12 8V4M17 8V5"/></svg>
                Dine-in
            </button>
            <select class="ctx-select" id="topTableSelect" aria-label="Select table">
                @foreach ($tables as $table)
                    <option value="{{ $table->id }}" data-code="{{ $table->code }}" @selected($defaultTable && $defaultTable->id === $table->id)>
                        Table {{ $table->code }}
                    </option>
                @endforeach
            </select>
            <div class="guest-stepper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <input type="number" id="guestCount" value="4" min="1" max="40" aria-label="Guest count">
            </div>
        </div>

        <label class="pos-search">
            <span class="search-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
            </span>
            <input type="search" id="globalSearch" placeholder="Search food, drinks or scan barcode..." autocomplete="off">
            <button type="button" class="scan-btn" title="Scan barcode" aria-label="Scan barcode">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2M4 17v2a1 1 0 0 0 1 1h2M16 4h2a1 1 0 0 1 1 1v2M20 16v2a1 1 0 0 1-1 1h-2M7 12h10"/></svg>
            </button>
        </label>

        <div class="pos-topbar-right">
            <div class="status-online"><span class="dot"></span> Online</div>
            <button type="button" class="icon-round" id="fullscreenBtn" title="Full page" aria-label="Toggle full page">
                <svg id="fsEnterIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                <svg id="fsExitIcon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3v3H5M16 3v3h3M8 21v-3H5M16 21v-3h3"/></svg>
            </button>
            <div class="notif-wrap">
                <button type="button" class="icon-round" id="notifBtn" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if ($notificationCount > 0)
                        <span class="badge">{{ $notificationCount }}</span>
                    @endif
                </button>
                <div class="notif-panel hidden" id="notifPanel">
                    <h4>Held Orders</h4>
                    @if ($heldOrders->isEmpty())
                        <p class="notif-empty">No held orders right now.</p>
                    @else
                        <ul>
                            @foreach ($heldOrders as $held)
                                <li>
                                    <button type="button" class="resume-held" data-id="{{ $held->id }}">
                                        <strong>{{ $held->order_number }}</strong>
                                        <small>{{ ucfirst($held->type) }} · {{ $held->items->count() }} items · ৳ {{ number_format((float) $held->total, 2) }}</small>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="profile-chip">
                <div class="av">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f28c28&color=fff&size=68" alt="">
                </div>
                <div class="meta">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $roleLabel }}</small>
                </div>
                <span class="chev">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
        </div>
    </header>

    <form class="pos-body" method="POST" action="{{ route('admin.pos.store') }}" id="posForm">
        @csrf
        <input type="hidden" name="action" id="posAction" value="pay">
        <input type="hidden" name="type" id="orderType" value="{{ $type }}">
        <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
        <input type="hidden" name="service_charge" id="serviceChargeInput" value="0">
        <input type="hidden" name="tax_amount" id="taxInput" value="0">
        <input type="hidden" name="tip_amount" id="tipInput" value="0">
        <input type="hidden" name="discount_amount" id="discountInput" value="0">
        <input type="hidden" name="table_id" id="tableIdInput" value="{{ $defaultTable?->id }}">
        <input type="hidden" name="guest_count" id="guestCountInput" value="4">
        <input type="hidden" name="customer_name" id="customerName" value="">
        <input type="hidden" name="customer_phone" id="customerPhone" value="">
        <input type="hidden" name="resume_order_id" id="resumeOrderId" value="">
        <div id="cartHiddenInputs"></div>

        <div class="pos-meta-hidden">
            <select name="customer_id" id="customerSelect">
                <option value="">Walk-in Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            <select name="meta" id="waiterSelect">
                <option value="">Unassigned</option>
                @foreach ($waiters as $waiter)
                    <option value="Waiter: {{ $waiter }}">{{ $waiter }}</option>
                @endforeach
            </select>
            <textarea name="notes" id="orderNotes"></textarea>
        </div>

        {{-- LEFT: CATALOG --}}
        <section class="catalog-panel">
            <div class="catalog-toolbar">
                <div class="cat-row" id="catTabs">
                    @foreach ($tabCats as $i => $cat)
                        <button type="button" class="cat-tab {{ $i === 0 ? 'active' : '' }} {{ $i >= 8 ? 'hidden-cat' : '' }}" data-category="{{ $cat === 'All Items' ? 'all' : $cat }}">
                            <span class="emoji">{{ $icons[$cat] ?? '🍴' }}</span>
                            {{ $cat }}
                        </button>
                    @endforeach
                    @if ($categories->count() > 7)
                        <button type="button" class="cat-tab more" id="moreCatsBtn">More ▾</button>
                    @endif
                </div>

                <div class="filter-row">
                    <div class="filter-pills" id="filterPills">
                        <button type="button" class="filter-pill active" data-filter="all">Popular</button>
                        <button type="button" class="filter-pill" data-filter="bestseller">Bestseller</button>
                        <button type="button" class="filter-pill" data-filter="new">New Arrival</button>
                        <button type="button" class="filter-pill" data-filter="spicy">Spicy</button>
                        <button type="button" class="filter-pill veg" data-filter="vegetarian">Vegetarian</button>
                    </div>
                    <div class="sort-wrap">
                        <label for="sortSelect">Sort by:</label>
                        <select id="sortSelect">
                            <option value="popularity">Popularity</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="name">Name A–Z</option>
                        </select>
                        <button type="button" class="filter-settings" title="Filter settings" aria-label="Filter settings">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="product-grid" id="menuGrid">
                @forelse ($menuItems as $item)
                    @php
                        $badge = $item->badge ?: ($item->is_bestseller ? 'Popular' : null);
                        $badgeKey = strtolower((string) preg_replace('/\s+/', '', (string) $badge));
                        $isVeg = (bool) $item->is_vegetarian;
                        $isNew = $badgeKey === 'new';
                        $isSpicy = $badgeKey === 'spicy';
                        $isBest = $item->is_bestseller || $badgeKey === 'bestseller';
                        $isPopular = $badgeKey === 'popular' || $item->is_bestseller || $item->is_favorite;
                        $image = $item->image_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80';
                    @endphp
                    <article
                        class="product-card"
                        data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                        data-price="{{ $item->price }}"
                        data-category="{{ $item->category }}"
                        data-popular="{{ $isPopular ? '1' : '0' }}"
                        data-bestseller="{{ $isBest ? '1' : '0' }}"
                        data-new="{{ $isNew ? '1' : '0' }}"
                        data-spicy="{{ $isSpicy ? '1' : '0' }}"
                        data-vegetarian="{{ $isVeg ? '1' : '0' }}"
                        data-favorite="{{ $item->is_favorite ? '1' : '0' }}"
                    >
                        <div class="thumb">
                            <img src="{{ $image }}" alt="{{ $item->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80'">
                            @if ($badge)
                                <span class="badge {{ $badgeKey }}">{{ $badge }}</span>
                            @endif
                            <span class="star {{ $item->is_favorite ? 'on' : '' }}" aria-hidden="true"></span>
                        </div>
                        <div class="info">
                            <p class="name">{{ $item->name }}</p>
                            @if ($item->description)
                                <p class="ingredients">{{ $item->description }}</p>
                            @else
                                <p class="ingredients muted">No ingredients listed</p>
                            @endif
                            <div class="foot">
                                <span class="price">৳ {{ number_format((float) $item->price, 2) }}</span>
                                <span class="add-btn" aria-hidden="true">+</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="cart-empty" style="grid-column:1/-1">
                        No menu items yet. Add dishes in Admin → Menu Items, then refresh.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- RIGHT: CHECKOUT --}}
        <aside class="checkout-panel">
            <div class="order-tabs" id="serviceType">
                <button type="button" class="{{ $type === 'dinein' ? 'active' : '' }}" data-type="dinein">Dine-in</button>
                <button type="button" class="{{ $type === 'takeaway' ? 'active' : '' }}" data-type="takeaway">Takeaway</button>
                <button type="button" class="{{ $type === 'delivery' ? 'active' : '' }}" data-type="delivery">Delivery</button>
            </div>

            <div class="checkout-head">
                <div class="order-id">
                    <span id="orderNumberLabel">Order #{{ $nextOrderNumber }}</span>
                    <button type="button" class="copy-btn" id="copyOrderBtn" title="Copy order number" aria-label="Copy order number">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>
                <button type="button" class="clear-btn" id="clearCart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    Clear All
                </button>
            </div>

            <div class="cart-scroll">
                <ul class="cart-lines" id="cartList">
                    <li class="cart-empty">No items yet. Tap + on a product to add.</li>
                </ul>
            </div>

            <div class="checkout-foot">
                <div class="promo-row">
                    <input type="text" name="promo_code" id="promoCode" placeholder="Enter promo code">
                    <button type="button" id="applyPromo">Apply</button>
                </div>

                <div class="totals-block">
                    <div><span>Subtotal</span><span id="subtotalLabel">৳ 0.00</span></div>
                    <div><span>Service Charge (<span id="serviceRateLabel">5</span>%)</span><span id="serviceLabel">৳ 0.00</span></div>
                    <div><span><span id="taxNameLabel">Tax (VAT</span> <span id="taxRateLabel">7</span>%)</span><span id="taxLabel">৳ 0.00</span></div>
                    <div id="discountRow" class="discount-line hidden"><span>Discount</span><span id="discountLabel">-৳ 0.00</span></div>
                    <div class="total-row">
                        <span>Total</span>
                        <span class="amount" id="totalLabel">৳ 0.00</span>
                    </div>
                </div>

                <div class="discount-row" id="discountPresets">
                    <button type="button" data-pct="5">5%</button>
                    <button type="button" data-pct="10">10%</button>
                    <button type="button" data-pct="15">15%</button>
                    <button type="button" data-pct="custom" id="customDiscountBtn">Custom</button>
                </div>

                <button type="button" class="pay-primary" id="payBtn">
                    Pay <span id="payAmount">৳ 0.00</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>

                <div class="checkout-actions">
                    <button type="submit" data-action="hold" id="holdBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                        Hold Order
                    </button>
                    <button type="submit" data-action="save" id="saveBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Save Order
                    </button>
                    <button type="button" id="printBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Bill
                    </button>
                </div>
            </div>
        </aside>
    </form>

    <footer class="pos-statusbar">
        <div class="stat-card">
            <div class="ico orange">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="data">
                <small>Items</small>
                <strong id="statItems">0</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="ico green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
            <div class="data">
                <small>Total</small>
                <strong id="statTotal">৳ 0.00</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="ico">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="data">
                <small>Customer</small>
                <strong id="statCustomer">Walk-in</strong>
            </div>
            <button type="button" class="action-btn" id="customerPickerBtn" title="Select customer">+</button>
        </div>
        <div class="stat-card">
            <div class="ico">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M8 4v4M16 4v4"/></svg>
            </div>
            <div class="data">
                <small>Table</small>
                <strong id="statTable">Table {{ $defaultTable?->code ?? '—' }}</strong>
            </div>
            <button type="button" class="action-btn" id="tablePickerBtn" title="Change table">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </button>
        </div>
        <div class="stat-card">
            <div class="ico">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div class="data">
                <small>Time</small>
                <strong id="statTime">--:-- --</strong>
            </div>
        </div>
    </footer>
</div>

<div class="pos-toast hidden" id="posToast"></div>

<div class="pos-overlay hidden" id="itemModal">
    <div class="pos-modal">
        <h3 id="itemModalTitle">Add note</h3>
        <p id="itemModalPrice" class="item-modal-price"></p>
        <div class="modifier-chips" id="modifierChips">
            <button type="button" data-mod="">None</button>
            <button type="button" data-mod="No Butter">No Butter</button>
            <button type="button" data-mod="Extra Cheese">Extra Cheese</button>
            <button type="button" data-mod="No Onion">No Onion</button>
            <button type="button" data-mod="Extra Spicy">Extra Spicy</button>
        </div>
        <label class="field-label">Special instructions</label>
        <input type="text" class="field" id="itemModalNote" placeholder="e.g. No Butter, Extra Cheese">
        <div class="modal-actions">
            <button type="button" id="cancelItemModal">Cancel</button>
            <button type="button" class="primary" id="confirmItemModal">Save note</button>
        </div>
    </div>
</div>

<div class="pos-overlay hidden" id="payModal">
    <div class="pos-modal">
        <h3>Complete Payment</h3>
        <label class="field-label" style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;color:#6b7a90;text-transform:uppercase;">Payment Method</label>
        <div class="pay-methods" id="payMethods">
            <button type="button" class="active" data-pay="cash">Cash</button>
            <button type="button" data-pay="card">Debit Card</button>
            <button type="button" data-pay="online">Online</button>
        </div>
        <div class="pay-customer-fields">
            <label class="field-label" style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;color:#6b7a90;text-transform:uppercase;">Customer name (optional)</label>
            <input type="text" class="field" id="modalCustomerName" placeholder="e.g. Walk-in / Guest name" autocomplete="name">
            <label class="field-label" style="display:block;margin:10px 0 6px;font-size:11px;font-weight:800;color:#6b7a90;text-transform:uppercase;">Phone number (optional)</label>
            <input type="tel" class="field" id="modalCustomerPhone" placeholder="e.g. 01XXXXXXXXX" autocomplete="tel">
        </div>
        <label class="field-label" style="display:block;margin:10px 0 6px;font-size:11px;font-weight:800;color:#6b7a90;text-transform:uppercase;">Notes (optional)</label>
        <textarea class="field" id="modalNotes" style="height:72px;padding:10px 12px;resize:vertical;" placeholder="Add order note..."></textarea>
        <div class="modal-actions">
            <button type="button" id="cancelPayModal">Cancel</button>
            <button type="button" class="primary" id="confirmPayBtn">Confirm Pay</button>
        </div>
    </div>
</div>

<div class="pos-overlay hidden" id="customerModal">
    <div class="pos-modal">
        <h3>Select Customer</h3>
        <select class="field" id="modalCustomerSelect">
            <option value="">Walk-in Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}">{{ $customer->name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>
            @endforeach
        </select>
        <div class="modal-actions">
            <button type="button" id="cancelCustomerModal">Cancel</button>
            <button type="button" class="primary" id="confirmCustomerBtn">Apply</button>
        </div>
    </div>
</div>

<div class="pos-overlay hidden" id="tableModal">
    <div class="pos-modal">
        <h3>Select Table</h3>
        <select class="field" id="modalTableSelect">
            @foreach ($tables as $table)
                <option value="{{ $table->id }}" data-code="{{ $table->code }}" @selected($defaultTable && $defaultTable->id === $table->id)>
                    Table {{ $table->code }} · {{ $table->zone }} ({{ $table->capacity }} seats)
                </option>
            @endforeach
        </select>
        <div class="modal-actions">
            <button type="button" id="cancelTableModal">Cancel</button>
            <button type="button" class="primary" id="confirmTableBtn">Apply</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $posRoot = rtrim(request()->root(), '/');
    $posJsPath = public_path('js/pos-app.js');
    $posJsVer = file_exists($posJsPath) ? filemtime($posJsPath) : time();
@endphp
<script>window.POS_CONFIG = {
    heldOrders: @json($heldOrdersPayload),
    vatRate: {{ (float) ($taxSettings['vat_rate'] ?? 7) }},
    serviceRate: {{ (float) ($taxSettings['service_charge_rate'] ?? 5) }},
    taxName: @json($taxSettings['tax_name'] ?? 'VAT')
};</script>
<script src="{{ $posRoot }}/js/pos-app.js?v={{ $posJsVer }}"></script>
@endpush
