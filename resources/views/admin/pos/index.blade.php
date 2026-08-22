@extends('admin.layouts.pos')

@section('content')
@php
    $type = in_array($defaultType, ['dinein','takeaway','delivery','qr','walkin'], true) ? $defaultType : 'dinein';
    if (in_array($type, ['walkin', 'qr'], true)) {
        $type = 'dinein';
    }
    $defaultTable = null; // seat is optional — assign later if needed
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

        <div class="pos-context" id="posContext">
            <button type="button" class="ctx-btn active" id="ctxTypeBtn" tabindex="-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M7 8V5M12 8V4M17 8V5"/></svg>
                <span id="ctxTypeLabel">{{ $type === 'takeaway' ? 'Takeaway' : ($type === 'delivery' ? 'Delivery' : 'Dine-in') }}</span>
            </button>
            <div class="dinein-only {{ in_array($type, ['takeaway', 'delivery'], true) ? 'hidden' : '' }}" id="dineinContext">
                <select class="ctx-select" id="topTableSelect" aria-label="Select table">
                    <option value="" data-code="">No table / seat later</option>
                    @foreach ($tables as $table)
                        @php $openOrderNo = $openTableOrderNumbers[$table->id] ?? null; @endphp
                        <option value="{{ $table->id }}" data-code="{{ $table->code }}" data-open-order="{{ $openOrderNo }}">
                            Table {{ $table->code }}{{ $openOrderNo ? ' · Open' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="type-hint {{ in_array($type, ['takeaway', 'delivery'], true) ? '' : 'hidden' }}" id="typeHint">
                <span id="typeHintText">{{ $type === 'delivery' ? 'Delivery order · no table' : 'Counter pickup · no table' }}</span>
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
            <button type="button" class="pay-first-toggle {{ ($settings->pay_first ?? false) ? 'is-on' : '' }}" id="payFirstToggle" title="Pay before kitchen receives the order" aria-pressed="{{ ($settings->pay_first ?? false) ? 'true' : 'false' }}">
                <span class="pf-label">Pay first</span>
                <span class="pf-switch" aria-hidden="true"></span>
                <strong class="pf-state" id="payFirstState">{{ ($settings->pay_first ?? false) ? 'ON' : 'OFF' }}</strong>
            </button>
            <button type="button" class="icon-round" id="fullscreenBtn" title="Extended form" aria-label="Toggle extended form" aria-pressed="false">
                <svg id="fsEnterIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                <svg id="fsExitIcon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3v3H5M16 3v3h3M8 21v-3H5M16 21v-3h3"/></svg>
            </button>
            <label class="pos-recall">
                <input type="search" id="recallOrderInput" placeholder="Find held order # or ID" autocomplete="off">
                <button type="button" id="recallOrderBtn">Find</button>
            </label>
            <button type="button" class="hold-count-chip" id="holdCountChip" title="Held orders">
                Hold <strong id="heldCountText">{{ $heldCount ?? $heldOrders->count() }}</strong>
            </button>
            <div class="notif-wrap">
                <button type="button" class="icon-round" id="notifBtn" aria-label="Held orders">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="badge {{ ($heldCount ?? $heldOrders->count()) < 1 ? 'hidden' : '' }}" id="heldCountBadge">{{ $heldCount ?? $heldOrders->count() }}</span>
                </button>
                <div class="notif-panel hidden" id="notifPanel">
                    <h4>Held orders · <span id="heldCountLabel">{{ $heldCount ?? $heldOrders->count() }}</span></h4>
                    <div class="notif-find">
                        <input type="search" id="heldFindInput" placeholder="Order number or ID" autocomplete="off">
                    </div>
                    <div id="heldOrdersWrap">
                    @if ($heldOrders->isEmpty())
                        <p class="notif-empty">No held orders right now.</p>
                    @else
                        <ul id="heldOrdersList">
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
                    <h4>Open Orders</h4>
                    @if (($openOrders ?? collect())->isEmpty())
                        <p class="notif-empty">No open kitchen orders.</p>
                    @else
                        <ul>
                            @foreach ($openOrders as $open)
                                <li>
                                    <button type="button" class="resume-open" data-id="{{ $open->id }}">
                                        <strong>Token {{ preg_match('/(\d+)$/', $open->order_number, $m) ? $m[1] : $open->order_number }}</strong>
                                        <small>
                                            {{ $open->table?->code ? 'Table '.$open->table->code : 'No seat' }}
                                            · {{ $open->items->count() }} items
                                            · ৳ {{ number_format((float) $open->total, 2) }}
                                        </small>
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
        <input type="hidden" name="pos_action" id="posAction" value="pay">
        <input type="hidden" name="type" id="orderType" value="{{ $type }}">
        <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
        <input type="hidden" name="cash_paid" id="cashPaidInput" value="0">
        <input type="hidden" name="bkash_paid" id="bkashPaidInput" value="0">
        <input type="hidden" name="card_paid" id="cardPaidInput" value="0">
        <input type="hidden" name="amount_tendered" id="amountTenderedInput" value="0">
        <input type="hidden" name="change_amount" id="changeAmountInput" value="0">
        <input type="hidden" name="service_charge" id="serviceChargeInput" value="0">
        <input type="hidden" name="tax_amount" id="taxInput" value="0">
        <input type="hidden" name="tip_amount" id="tipInput" value="0">
        <input type="hidden" name="discount_amount" id="discountInput" value="0">
        <input type="hidden" name="apply_service" id="applyServiceInput" value="0">
        <input type="hidden" name="apply_tax" id="applyTaxInput" value="0">
        <input type="hidden" name="service_rate" id="serviceRateInput" value="{{ (float) ($taxSettings['service_charge_rate'] ?? 0) }}">
        <input type="hidden" name="tax_rate" id="taxRateInput" value="{{ (float) ($taxSettings['vat_rate'] ?? 0) }}">
        <input type="hidden" name="table_id" id="tableIdInput" value="">
        <input type="hidden" name="guest_count" id="guestCountInput" value="">
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
                        <button type="button" class="filter-settings" id="refreshMenuBtn" title="Refresh menu from admin" aria-label="Refresh menu">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 3v6h-6"/></svg>
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
            <div class="checkout-compact">
                <div class="order-tabs" id="serviceType">
                    <button type="button" class="{{ $type === 'dinein' ? 'active' : '' }}" data-type="dinein">Dine-in</button>
                    <button type="button" class="{{ $type === 'takeaway' ? 'active' : '' }}" data-type="takeaway">Takeaway</button>
                    <button type="button" class="{{ $type === 'delivery' ? 'active' : '' }}" data-type="delivery">Delivery</button>
                </div>

                <div class="checkout-toolbar">
                    <div class="order-number-row">
                        <span id="orderNumberLabel">#{{ $nextOrderNumber }}</span>
                        <button type="button" class="icon-btn" id="copyOrderBtn" title="Copy order number" aria-label="Copy order number">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <button type="button" class="icon-btn danger" id="clearCart" title="Clear cart" aria-label="Clear cart">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                        </button>
                    </div>
                    <div class="guest-inline">
                        <input type="text" class="field" id="guestNameField" placeholder="Customer name" maxlength="120" autocomplete="name">
                        <input type="tel" class="field" id="guestPhoneField" placeholder="Phone" maxlength="40" autocomplete="tel">
                    </div>
                </div>
            </div>

            <div class="cart-section-head">
                <span>Items</span>
                <div class="cart-head-actions">
                    <span class="cart-count" id="cartCountLabel">0 items</span>
                    <button type="button" class="cart-expand-btn" id="expandCartBtn" title="View all items" aria-label="Expand cart">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                    </button>
                </div>
            </div>

            <div class="cart-scroll">
                <ul class="cart-lines" id="cartList">
                    <li class="cart-empty">No items yet. Tap + on a menu item to add.</li>
                </ul>
            </div>

            <div class="checkout-foot">
                <div class="checkout-foot-scroll">
                    <div class="promo-row">
                        <input type="text" name="promo_code" id="promoCode" placeholder="Promo code">
                        <button type="button" id="applyPromo">Apply</button>
                    </div>

                    <div class="bill-summary">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span id="subtotalLabel">৳ 0.00</span>
                        </div>

                        <div class="adjustment-row" id="serviceChargeRow">
                            <label class="adjustment-toggle">
                                <input type="checkbox" id="applyServiceToggle">
                                <span class="toggle-ui" aria-hidden="true"></span>
                                <span class="adjustment-copy">Service charge</span>
                            </label>
                            <div class="adjustment-meta">
                                <label class="rate-pill" title="Service charge rate">
                                    <input type="number" id="serviceRateField" min="0" max="100" step="0.1" value="{{ (float) ($taxSettings['service_charge_rate'] ?? 0) }}" disabled>
                                    <span>%</span>
                                </label>
                                <span class="adjustment-amount" id="serviceLabel">৳ 0.00</span>
                            </div>
                        </div>

                        <div class="adjustment-row" id="taxChargeRow">
                            <label class="adjustment-toggle">
                                <input type="checkbox" id="applyTaxToggle">
                                <span class="toggle-ui" aria-hidden="true"></span>
                                <span class="adjustment-copy" id="taxNameLabel">{{ $taxSettings['tax_name'] ?? 'VAT' }}</span>
                            </label>
                            <div class="adjustment-meta">
                                <label class="rate-pill" title="Tax rate">
                                    <input type="number" id="taxRateField" min="0" max="100" step="0.1" value="{{ (float) ($taxSettings['vat_rate'] ?? 0) }}" disabled>
                                    <span>%</span>
                                </label>
                                <span class="adjustment-amount" id="taxLabel">৳ 0.00</span>
                            </div>
                        </div>

                        <div id="discountRow" class="summary-line discount-line hidden">
                            <span>Discount</span>
                            <span id="discountLabel">−৳ 0.00</span>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>
                            <span class="amount" id="totalLabel">৳ 0.00</span>
                        </div>
                    </div>

                    <div class="discount-presets">
                        <div class="discount-row" id="discountPresets">
                            <button type="button" data-pct="5">5%</button>
                            <button type="button" data-pct="10">10%</button>
                            <button type="button" data-pct="15">15%</button>
                            <button type="button" data-pct="custom" id="customDiscountBtn">Custom</button>
                        </div>
                    </div>
                </div>

                <div class="checkout-actions-bar">
                    <button type="button" class="pay-primary" id="payBtn">
                        Pay <span id="payAmount">৳ 0.00</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="checkout-actions">
                        <button type="submit" data-action="hold" id="holdBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            Hold
                        </button>
                        <button type="submit" data-action="save" id="saveBtn" title="Print slips now · save as unpaid hold until Pay">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Send
                        </button>
                        <button type="button" id="printBtn" title="Print due bill for guest">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Due Bill
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    </form>

    {{-- Due bill preview — must live inside .pos-shell for fullscreen mode --}}
    <div class="due-bill-preview hidden" id="dueBillPreview" role="dialog" aria-modal="true" aria-label="Due bill preview">
        <div class="due-bill-preview-backdrop" id="dueBillBackdrop"></div>
        <div class="due-bill-preview-panel">
            <div class="due-bill-preview-toolbar">
                <div>
                    <strong>Guest Due Bill</strong>
                    <small>Review before printing for the table</small>
                </div>
                <div class="due-bill-preview-actions">
                    <button type="button" id="closeDueBillPreview">Close</button>
                    <button type="button" class="primary" id="confirmDueBillPrint">Print</button>
                </div>
            </div>
            <div class="due-bill-preview-scroll">
                <div id="dueBillSheet" class="due-bill-sheet due-bill-sheet--live">
                    <div class="due-bill">
                        <header class="due-bill-head">
                            <div class="slip-crest">✦</div>
                            <div class="due-bill-brand" id="billRestaurantName">{{ $restaurant['name'] ?? 'Bynnas Restora' }}</div>
                            <div class="due-bill-tagline" id="billTagline">{{ $restaurant['tagline'] ?? '' }}</div>
                            <div class="slip-rule"></div>
                            <div class="due-bill-contact" id="billAddress">{{ $restaurant['address'] ?? '' }}</div>
                            <div class="due-bill-contact" id="billPhone"></div>
                        </header>

                        <div class="due-bill-badge">Due bill</div>

                        <div class="due-bill-meta">
                            <div><span>Order</span><strong id="billOrderNo">—</strong></div>
                            <div><span>Date</span><strong id="billDate">—</strong></div>
                            <div><span>Type</span><strong id="billType">—</strong></div>
                            <div id="billTableRow"><span>Table</span><strong id="billTable">—</strong></div>
                            <div id="billGuestsRow"><span>Guests</span><strong id="billGuests">—</strong></div>
                            <div><span>Customer</span><strong id="billCustomer">Walk-in</strong></div>
                            <div class="span-2"><span>Cashier</span><strong id="billCashier">{{ $user->name }}</strong></div>
                        </div>

                        <table class="due-bill-items">
                            <thead>
                                <tr>
                                    <th class="qty">Qty</th>
                                    <th>Item</th>
                                    <th class="amt">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="billItemsBody"></tbody>
                        </table>

                        <div class="due-bill-totals">
                            <div><span>Subtotal</span><strong id="billSubtotal">৳ 0.00</strong></div>
                            <div id="billServiceRow" class="hidden"><span id="billServiceLabel">Service</span><strong id="billService">৳ 0.00</strong></div>
                            <div id="billTaxRow" class="hidden"><span id="billTaxLabel">Tax</span><strong id="billTax">৳ 0.00</strong></div>
                            <div id="billDiscountRow" class="hidden"><span>Discount</span><strong id="billDiscount">-৳ 0.00</strong></div>
                            <div class="due-total"><span>Amount Due</span><strong id="billTotal">৳ 0.00</strong></div>
                        </div>

                        <div class="due-bill-status">
                            <span>Payment Status</span>
                            <strong>UNPAID · PLEASE PAY AT COUNTER</strong>
                        </div>

                        <p class="due-bill-note" id="billNotes" hidden></p>

                        <footer class="due-bill-foot">
                            <p>Thank you for dining with us.</p>
                            <p>Please present this bill when paying.</p>
                            <p class="tiny" id="billPrintedAt"></p>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dual tokens: customer + kitchen (auto-print after Send) --}}
    <div id="tokenPrintSheet" class="token-print-sheet" aria-hidden="true">
        <div class="token-slip token-slip--customer" id="customerTokenSlip">
            <div class="slip-crest">✦</div>
            <div class="token-brand">{{ $restaurant['name'] ?? 'Bynnas Restora' }}</div>
            <div class="token-tagline">Fine dining · Hospitality</div>
            <div class="slip-rule"></div>
            <div class="token-badge">Guest token</div>
            <div class="token-number" id="tokCustomerNo">—</div>
            <div class="token-meta">
                <div><span>Order</span><strong id="tokCustomerOrder">—</strong></div>
                <div><span>Seat</span><strong id="tokCustomerSeat">No seat yet</strong></div>
                <div><span>Service</span><strong id="tokCustomerType">—</strong></div>
                <div><span>Time</span><strong id="tokCustomerTime">—</strong></div>
            </div>
            <ul class="token-items" id="tokCustomerItems"></ul>
            <div class="slip-rule"></div>
            <p class="token-note">Please keep this token. Present it when collecting your order or settling the bill.</p>
            <p class="token-foot" id="tokCustomerFoot"></p>
        </div>

        <div class="token-slip token-slip--kitchen" id="kitchenTokenSlip">
            <div class="token-brand kitchen-brand">Kitchen</div>
            <div class="token-badge" id="tokKitchenBadge">NEW ORDER</div>
            <div class="token-number" id="tokKitchenNo">—</div>
            <div class="token-meta">
                <div><span>Order</span><strong id="tokKitchenOrder">—</strong></div>
                <div><span>Seat</span><strong id="tokKitchenSeat">No seat yet</strong></div>
                <div><span>Service</span><strong id="tokKitchenType">—</strong></div>
                <div><span>Time</span><strong id="tokKitchenTime">—</strong></div>
            </div>
            <ul class="token-items token-items--kitchen" id="tokKitchenItems"></ul>
            <p class="token-notes" id="tokKitchenNotes"></p>
            <div class="slip-rule"></div>
            <p class="token-foot">Prepare · pass · call waiter</p>
        </div>
    </div>

    {{-- Paid invoice (auto-print after successful payment) --}}
    <div id="paidInvoiceSheet" class="paid-invoice-sheet" aria-hidden="true">
        <div class="due-bill paid-invoice">
            <header class="due-bill-head">
                <div class="slip-crest">✦</div>
                <div class="due-bill-brand" id="invRestaurantName">{{ $restaurant['name'] ?? 'Bynnas Restora' }}</div>
                <div class="due-bill-tagline" id="invTagline">{{ $restaurant['tagline'] ?? '' }}</div>
                <div class="slip-rule"></div>
                <div class="due-bill-contact" id="invAddress">{{ $restaurant['address'] ?? '' }}</div>
                <div class="due-bill-contact" id="invPhone"></div>
            </header>
            <div class="due-bill-badge paid">Paid invoice</div>
            <div class="due-bill-meta">
                <div><span>Invoice</span><strong id="invOrderNo">—</strong></div>
                <div><span>Date</span><strong id="invDate">—</strong></div>
                <div><span>Type</span><strong id="invType">—</strong></div>
                <div id="invTableRow"><span>Table</span><strong id="invTable">—</strong></div>
                <div><span>Customer</span><strong id="invCustomer">Walk-in</strong></div>
                <div><span>Phone</span><strong id="invCustomerPhone">—</strong></div>
                <div class="span-2"><span>Cashier</span><strong id="invCashier">{{ $user->name }}</strong></div>
            </div>
            <table class="due-bill-items">
                <thead>
                    <tr>
                        <th class="qty">Qty</th>
                        <th>Item</th>
                        <th class="amt">Amount</th>
                    </tr>
                </thead>
                <tbody id="invItemsBody"></tbody>
            </table>
            <div class="due-bill-totals">
                <div><span>Subtotal</span><strong id="invSubtotal">৳ 0.00</strong></div>
                <div id="invServiceRow" class="hidden"><span>Service</span><strong id="invService">৳ 0.00</strong></div>
                <div id="invTaxRow" class="hidden"><span>Tax</span><strong id="invTax">৳ 0.00</strong></div>
                <div id="invDiscountRow" class="hidden"><span>Discount</span><strong id="invDiscount">-৳ 0.00</strong></div>
                <div class="due-total"><span>Total Paid</span><strong id="invTotal">৳ 0.00</strong></div>
            </div>
            <div class="inv-pay-breakdown" id="invPayBreakdown"></div>
            <div class="due-bill-status paid">
                <span>Payment Status</span>
                <strong>PAID</strong>
            </div>
            <footer class="due-bill-foot">
                <p>Thank you for your payment.</p>
                <p class="tiny" id="invPrintedAt"></p>
            </footer>
        </div>
    </div>

    <div class="pos-toast hidden" id="posToast"></div>

    <div class="slip-preview hidden" id="slipPreview" role="dialog" aria-modal="true" aria-label="Slip preview">
        <div class="slip-preview-backdrop" id="slipPreviewBackdrop"></div>
        <div class="slip-preview-panel">
            <div class="slip-preview-toolbar">
                <div>
                    <strong id="slipPreviewTitle">Slip</strong>
                    <small>Look over the slip, then press Enter to print</small>
                </div>
                <div class="slip-preview-actions">
                    <button type="button" id="slipPreviewClose">Close</button>
                    <button type="button" class="primary" id="slipPreviewPrint">Print</button>
                </div>
            </div>
            <div class="slip-preview-scroll" id="slipPreviewStage"></div>
            <p class="slip-preview-hint">Enter = print &nbsp;·&nbsp; Esc = close</p>
        </div>
    </div>

    {{-- All modals must live inside .pos-shell so they appear in fullscreen --}}
    <div class="pos-overlay hidden" id="cartModal">
        <div class="pos-modal cart-modal">
            <div class="cart-modal-head">
                <div>
                    <h3>Order items</h3>
                    <p id="cartModalCount">0 items · add a note on any item</p>
                </div>
                <button type="button" class="cart-modal-close" id="closeCartModal" aria-label="Close">&times;</button>
            </div>
            <div class="cart-modal-scroll">
                <ul class="cart-modal-list" id="cartModalList"></ul>
            </div>
            <div class="cart-modal-foot">
                <div class="cart-modal-total">
                    <span>Subtotal</span>
                    <strong id="cartModalSubtotal">৳ 0.00</strong>
                </div>
                <button type="button" class="primary" id="closeCartModalBtn">Done</button>
            </div>
        </div>
    </div>

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
        <div class="pos-modal pay-modal">
            <div class="pay-modal-head">
                <div>
                    <h3>Collect payment</h3>
                    <p>Choose a method, then confirm</p>
                </div>
                <button type="button" class="cart-modal-close" id="cancelPayModal" aria-label="Close">&times;</button>
            </div>
            <div class="pay-modal-body">
                <div class="pay-due-box">
                    <span>Amount due</span>
                    <strong id="payDueAmount">৳ 0.00</strong>
                </div>

                <div class="pay-quick">
                    <button type="button" data-fill="cash">Cash</button>
                    <button type="button" data-fill="bkash">bKash</button>
                    <button type="button" data-fill="card">Card</button>
                </div>

                <div class="pay-split-grid">
                    <label>
                        <span>Cash on bill</span>
                        <input type="number" class="field" id="payCash" min="0" step="0.01" placeholder="0.00" readonly tabindex="-1">
                    </label>
                    <label>
                        <span>bKash</span>
                        <input type="number" class="field" id="payBkash" min="0" step="0.01" placeholder="0.00">
                    </label>
                    <label>
                        <span>Card</span>
                        <input type="number" class="field" id="payCard" min="0" step="0.01" placeholder="0.00">
                    </label>
                </div>

                <div class="pay-balance-row">
                    <span>Allocated</span>
                    <strong id="payAllocated">৳ 0.00</strong>
                    <span>Still due</span>
                    <strong id="payRemaining">৳ 0.00</strong>
                </div>

                <div class="pay-tender-grid">
                    <label>
                        <span>Cash received</span>
                        <input type="number" class="field" id="payTendered" min="0" step="0.01" placeholder="0.00">
                    </label>
                    <div class="pay-change-box">
                        <span>Change</span>
                        <strong id="payChange">৳ 0.00</strong>
                    </div>
                </div>

                <p class="pay-error hidden" id="payError"></p>

                <div class="pay-guest-grid">
                    <label>
                        <span>Guest name</span>
                        <input type="text" class="field" id="modalCustomerName" placeholder="Walk-in" autocomplete="name">
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" class="field" id="modalCustomerPhone" placeholder="01XXXXXXXXX" autocomplete="tel">
                    </label>
                </div>
                <label class="pay-notes">
                    <span>Note</span>
                    <textarea class="field" id="modalNotes" placeholder="Optional kitchen / bill note"></textarea>
                </label>
            </div>
            <div class="pay-modal-foot">
                <button type="button" id="cancelPayModalBtn">Cancel</button>
                <button type="button" class="primary" id="confirmPayBtn">Pay &amp; print</button>
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
                    <option value="" data-code="">No table / seat later</option>
                    @foreach ($tables as $table)
                        @php $openOrderNo = $openTableOrderNumbers[$table->id] ?? null; @endphp
                        <option value="{{ $table->id }}" data-code="{{ $table->code }}" data-open-order="{{ $openOrderNo }}">
                            Table {{ $table->code }} · {{ $table->zone }} ({{ $table->capacity }} seats){{ $openOrderNo ? ' · Open order' : '' }}
                        </option>
                    @endforeach
                </select>
            <div class="modal-actions">
                <button type="button" id="cancelTableModal">Cancel</button>
                <button type="button" class="primary" id="confirmTableBtn">Apply</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $posRoot = rtrim(request()->root(), '/');
    $posJsPath = public_path('js/pos-app.js');
    $posJsVer = file_exists($posJsPath) ? filemtime($posJsPath) : time();
    $posRestaurant = $restaurant ?? [
        'name' => 'Bynnas Restora',
        'tagline' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'currency' => '৳',
    ];
@endphp
<script>window.POS_CONFIG = {
    heldOrders: @json($heldOrdersPayload),
    openOrders: @json($openOrdersPayload ?? []),
    tableOrderUrl: @json(route('admin.pos.table-order')),
    findOrderUrl: @json(route('admin.pos.find-order')),
    catalogUrl: @json(route('admin.pos.catalog')),
    nextOrderNumber: @json($nextOrderNumber),
    vatRate: {{ (float) ($taxSettings['vat_rate'] ?? 0) }},
    serviceRate: {{ (float) ($taxSettings['service_charge_rate'] ?? 0) }},
    taxName: @json($taxSettings['tax_name'] ?? 'VAT'),
    applyServiceDefault: false,
    applyTaxDefault: false,
    payFirst: @json((bool) ($settings->pay_first ?? false)),
    payFirstUrl: @json(route('admin.settings.pay-first')),
    restaurant: @json($posRestaurant),
    cashierName: @json($user->name),
    invoice: @json(session('invoice')),
    tokens: @json(session('tokens'))
};</script>
<script>
(function () {
    function fsEl() { return document.fullscreenElement || document.webkitFullscreenElement || null; }
    function applyExtended(on) {
        document.documentElement.classList.toggle('pos-extended', on);
        document.body.classList.toggle('pos-extended', on);
        var shell = document.querySelector('.pos-shell');
        if (shell) shell.classList.toggle('pos-extended', on);
        var enter = document.getElementById('fsEnterIcon');
        var exit = document.getElementById('fsExitIcon');
        var btn = document.getElementById('fullscreenBtn');
        if (enter) enter.classList.toggle('hidden', on);
        if (exit) exit.classList.toggle('hidden', !on);
        if (btn) {
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.title = on ? 'Exit extended (Esc)' : 'Extended form';
        }
    }
    window.__posWantExtended = false;
    window.__posPrinting = false;
    window.__posApplyExtended = applyExtended;
    window.__posIsExtended = function () {
        return !!(fsEl() || document.documentElement.classList.contains('pos-extended') || window.__posWantExtended);
    };
    window.__posEnterExtended = function () {
        window.__posWantExtended = true;
        applyExtended(true);
        if (fsEl()) return;
        var node = document.documentElement;
        var req = node.requestFullscreen || node.webkitRequestFullscreen;
        if (typeof req !== 'function') return;
        try {
            var result = req.call(node);
            if (result && typeof result.then === 'function') {
                result.then(function () { applyExtended(true); }).catch(function () { applyExtended(true); });
            }
        } catch (err) {
            applyExtended(true);
        }
    };
    window.__posLeaveExtended = function () {
        window.__posWantExtended = false;
        var exit = document.exitFullscreen || document.webkitExitFullscreen;
        if (exit && fsEl()) {
            try { exit.call(document); } catch (err) {}
        }
        applyExtended(false);
    };
    window.__posToggleExtended = function (e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        if (window.__posWantExtended || fsEl() || document.documentElement.classList.contains('pos-extended')) {
            window.__posLeaveExtended();
            return;
        }
        window.__posEnterExtended();
    };
    window.__posRestoreExtended = function () {
        if (!window.__posWantExtended) return;
        applyExtended(true);
        if (fsEl()) return;
        var node = document.documentElement;
        var req = node.requestFullscreen || node.webkitRequestFullscreen;
        if (typeof req !== 'function') return;
        try {
            var result = req.call(node);
            if (result && typeof result.then === 'function') {
                result.catch(function () { applyExtended(true); });
            }
        } catch (err) {}
    };
    document.addEventListener('click', function (e) {
        if (!e.target.closest || !e.target.closest('#fullscreenBtn')) return;
        window.__posToggleExtended(e);
    });
    document.addEventListener('fullscreenchange', function () {
        if (fsEl()) {
            applyExtended(true);
            return;
        }
        if (window.__posWantExtended || window.__posPrinting) {
            applyExtended(true);
            return;
        }
        applyExtended(false);
    });
    document.addEventListener('webkitfullscreenchange', function () {
        if (fsEl()) {
            applyExtended(true);
            return;
        }
        if (window.__posWantExtended || window.__posPrinting) {
            applyExtended(true);
            return;
        }
        applyExtended(false);
    });
})();
</script>
<script src="{{ $posRoot }}/js/pos-app.js?v={{ $posJsVer }}"></script>
@endpush
