@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Add Recipe' : 'Edit Recipe')

@section('content')
@php
    $units = ['portion', 'plate', 'serving', 'kg', 'g', 'L', 'ml', 'pcs', 'cup', 'tbsp'];
    $yieldUnits = ['Portion', 'Plate', 'Serving', 'Batch', 'kg', 'L'];
@endphp

<form
    id="recipeBomForm"
    class="bom-page"
    method="POST"
    action="{{ $mode === 'create' ? route('admin.recipes.store') : route('admin.recipes.update', $recipe) }}"
    data-mode="{{ $mode }}"
    data-recipe-id="{{ $recipe->id ?? '' }}"
    data-check-name-url="{{ route('admin.recipes.check-name') }}"
>
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <input type="hidden" name="action" id="formAction" value="save">

    <div class="bom-top">
        <a href="{{ route('admin.recipes.index') }}" class="bom-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            Back to Recipes
        </a>
        <div class="page-head bom-head">
            <div>
                <h1>{{ $mode === 'create' ? 'Add Recipe' : 'Edit Recipe' }}</h1>
                <p>Define ingredient quantities to calculate accurate food cost and maintain inventory.</p>
            </div>
        </div>
    </div>

    <div class="bom-layout">
        <div class="bom-main">
            <section class="card bom-card">
                <div class="bom-card-head">
                    <h2>Recipe Details</h2>
                </div>
                <div class="bom-details-grid">
                    <label class="bom-field">
                        <span>Recipe Name <em>*</em></span>
                        <div class="bom-input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                            <input class="field" name="name" id="recipeName" value="{{ old('name', $recipe->name) }}" placeholder="e.g. Grilled Salmon" required>
                        </div>
                    </label>
                    <label class="bom-field">
                        <span>Recipe Code <em>*</em></span>
                        <div class="bom-input-wrap">
                            <span class="bom-hash">#</span>
                            <input class="field bom-code-field" name="code" id="recipeCode" value="{{ old('code', $recipe->code) }}" placeholder="RC-001" required>
                        </div>
                    </label>
                    <label class="bom-field">
                        <span>Yield Quantity <em>*</em></span>
                        <input class="field" type="number" min="1" step="1" name="yield_qty" id="yieldQty" value="{{ old('yield_qty', $recipe->yield_qty ?: 1) }}" required>
                    </label>
                    <label class="bom-field">
                        <span>Yield Unit</span>
                        <select class="field" name="yield_unit" id="yieldUnit" required>
                            @foreach ($yieldUnits as $yu)
                                <option value="{{ strtolower($yu) }}" @selected(strtolower((string) old('yield_unit', $recipe->yield_unit)) === strtolower($yu))>{{ $yu }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="bom-field">
                        <span>Status</span>
                        <select class="field" name="status" id="recipeStatus">
                            <option value="active" @selected(old('status', $recipe->status) === 'active')>Active</option>
                            <option value="draft" @selected(old('status', $recipe->status) === 'draft')>Draft</option>
                            <option value="inactive" @selected(old('status', $recipe->status) === 'inactive')>Inactive</option>
                        </select>
                    </label>
                    <label class="bom-field span-all">
                        <span>Notes</span>
                        <textarea class="field" name="notes" id="recipeNotes" rows="3" placeholder="Optional cooking notes, allergens, plating tips...">{{ old('notes', $recipe->notes) }}</textarea>
                    </label>
                </div>

                <div class="bom-yield-tip" id="yieldExamplePanel">
                    <button type="button" class="bom-yield-tip-toggle" id="yieldHintToggle" aria-expanded="true">
                        <span class="bom-yield-tip-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2Z"/></svg>
                        </span>
                        <span class="bom-yield-tip-copy">
                            <strong>Yield example</strong>
                            <span>If ingredients make 1 plate, set yield to <b>1</b>. If they make a batch of 4 plates, set yield to <b>4</b> — cost and stock are split automatically.</span>
                        </span>
                        <svg class="bom-yield-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="bom-yield-tip-body">
                        <div class="bom-yield-mini">
                            <div>
                                <span class="bom-pill">Yield 1</span>
                                Ingredients = 1 portion · sell 1 → deduct 100%
                            </div>
                            <div>
                                <span class="bom-pill gold">Yield 4</span>
                                Ingredients = 4 portions · sell 1 → deduct 25%
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card bom-card">
                <div class="bom-card-head">
                    <div>
                        <h2>Ingredients (Bill of Materials)</h2>
                        <p>Add ingredients and quantities required for this recipe.</p>
                    </div>
                    <div class="bom-import">
                        <select id="importRecipeSelect" class="field" aria-label="Import from recipe">
                            <option value="">Import from Recipe</option>
                            @foreach ($importRecipes as $ir)
                                <option value="{{ $ir->id }}">{{ $ir->name }}@if($ir->code) ({{ $ir->code }})@endif</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn bom-import-btn" id="importRecipeBtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                            Import
                        </button>
                    </div>
                </div>

                <div class="bom-table-wrap">
                    <table class="bom-table" id="bomTable">
                        <thead>
                            <tr>
                                <th class="col-num">#</th>
                                <th>Ingredient</th>
                                <th class="col-cost">Unit Cost</th>
                                <th class="col-qty">Quantity</th>
                                <th class="col-unit">Unit</th>
                                <th class="col-total">Total Cost (৳)</th>
                                <th class="col-act"></th>
                            </tr>
                        </thead>
                        <tbody id="ingredientRows">
                            @foreach (old('ingredients', $ingredientRows) as $i => $row)
                                <tr class="bom-row" data-index="{{ $i }}">
                                    <td class="bom-idx">{{ $i + 1 }}</td>
                                    <td>
                                        <select class="field bom-item" name="ingredients[{{ $i }}][inventory_item_id]" required>
                                            <option value="">Select ingredient</option>
                                            @foreach ($items as $item)
                                                <option
                                                    value="{{ $item->id }}"
                                                    data-unit="{{ $item->costUnit() }}"
                                                    data-cost="{{ $item->unit_cost }}"
                                                    @selected((string) ($row['inventory_item_id'] ?? '') === (string) $item->id)
                                                >{{ $item->name }} ({{ $item->sku }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="field bom-unit-cost" type="text" value="" readonly tabindex="-1">
                                        <small class="bom-conv-note" hidden></small>
                                    </td>
                                    <td><input class="field bom-qty" type="number" min="0.001" step="0.001" name="ingredients[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? '' }}" placeholder="0.00" required></td>
                                    <td>
                                        <select class="field bom-unit" name="ingredients[{{ $i }}][unit]" data-preferred="{{ $row['unit'] ?? '' }}">
                                            <option value="">Select ingredient first</option>
                                        </select>
                                    </td>
                                    <td><input class="field bom-line-total" type="text" value="৳ 0.00" readonly tabindex="-1"></td>
                                    <td>
                                        <button type="button" class="bom-remove" title="Remove ingredient" aria-label="Remove">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bom-table-foot">
                    <button type="button" class="btn bom-add" id="addIngredient">+ Add Ingredient</button>
                    <div class="bom-ingredients-total">
                        Total Ingredients Cost <strong id="ingredientsTotalLabel">৳ 0.00</strong>
                    </div>
                </div>
            </section>

            <section class="card bom-card bom-pricing-card">
                <div class="bom-card-head">
                    <div>
                        <h2>Pricing</h2>
                        <p>Profit % is margin on selling price: selling = cost ÷ (1 − margin%). Or type selling price and the margin updates.</p>
                    </div>
                    <span class="bom-pricing-badge" id="pricingModeNote">Manual price</span>
                </div>
                <div class="bom-pricing-body">
                    <div class="bom-price-tile is-readonly">
                        <span class="bom-price-label">Cost / portion</span>
                        <div class="bom-price-value">
                            <em>৳</em>
                            <input type="text" id="costPerPortionDisplay" value="0.00" readonly tabindex="-1">
                        </div>
                    </div>
                    <div class="bom-price-tile">
                        <span class="bom-price-label">Desired profit margin %</span>
                        <div class="bom-price-value">
                            <input type="number" min="0" max="99.9" step="0.1" id="profitPercent" placeholder="30" value="">
                            <em class="suffix">%</em>
                        </div>
                    </div>
                    <div class="bom-price-tile is-primary">
                        <span class="bom-price-label">Selling price <i>*</i></span>
                        <div class="bom-price-value">
                            <em>৳</em>
                            <input type="number" min="0" step="0.01" name="selling_price" id="sellingPrice" value="{{ old('selling_price', $recipe->selling_price ?? 0) }}" required>
                        </div>
                    </div>
                    <div class="bom-price-tile is-result">
                        <span class="bom-price-label">Profit / portion</span>
                        <strong id="profitAmountLabel">৳ 0.00</strong>
                    </div>
                </div>
                <p class="bom-pricing-formula" id="pricingFormulaHint">Selling = cost ÷ (1 − margin%)</p>
            </section>

            <div class="bom-actions">
                <button type="reset" class="btn" id="resetForm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset
                </button>
                <div class="bom-actions-right">
                    <button type="submit" class="btn" id="saveDraftBtn">Save as Draft</button>
                    <button type="submit" class="btn btn-gold" id="saveRecipeBtn">Save Recipe</button>
                </div>
            </div>
        </div>

        <aside class="bom-side">
            <section class="card bom-side-card preview">
                <h3>Recipe Preview</h3>
                <p class="bom-preview-yield">This recipe will yield <strong id="previewYield">1 Portion</strong></p>
                <p class="bom-preview-hint" id="previewYieldHint">Ingredients below = 1 serving</p>
                <div class="bom-preview-cost">
                    <span>Estimated Cost per Portion</span>
                    <strong id="previewCostPer">৳ 0.00</strong>
                </div>
            </section>

            <section class="card bom-side-card summary">
                <h3>Cost Summary</h3>
                <div class="bom-summary-rows">
                    <div class="bom-summary-row">
                        <span>Total Ingredients Cost</span>
                        <strong id="sumIngredients">৳ 0.00</strong>
                    </div>
                    <label class="bom-summary-row editable">
                        <span>Packaging Cost</span>
                        <div class="bom-mini-money">
                            <span>৳</span>
                            <input type="number" min="0" step="0.01" name="packaging_cost" id="packagingCost" value="{{ old('packaging_cost', $recipe->packaging_cost ?? 0) }}">
                        </div>
                    </label>
                    <label class="bom-summary-row editable">
                        <span>Other Cost</span>
                        <div class="bom-mini-money">
                            <span>৳</span>
                            <input type="number" min="0" step="0.01" name="other_cost" id="otherCost" value="{{ old('other_cost', $recipe->other_cost ?? 0) }}">
                        </div>
                    </label>
                    <div class="bom-summary-row total">
                        <span>Total Cost per Portion</span>
                        <strong id="sumPerPortion">৳ 0.00</strong>
                    </div>
                    <div class="bom-summary-row">
                        <span>Selling Price</span>
                        <strong id="sumSelling">৳ 0.00</strong>
                    </div>
                    <div class="bom-summary-row">
                        <span>Profit / Portion</span>
                        <strong id="sumProfit">৳ 0.00</strong>
                    </div>
                    <div class="bom-summary-row margin">
                        <span>Profit Margin</span>
                        <strong id="sumMargin">0%</strong>
                    </div>
                </div>
            </section>

            <section class="card bom-side-card tips">
                <h3>Tips</h3>
                <ul>
                    <li>Use accurate ingredient quantities for precise food cost.</li>
                    <li>Unit costs come from live inventory automatically.</li>
                    <li>Link this recipe to a menu item so POS sales deduct stock.</li>
                    <li>Save as Draft until ingredients are finalized.</li>
                </ul>
            </section>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
window.recipeBomData = {
    items: @json($inventoryPayload),
    imports: @json($importPayload),
    units: @json($units)
};
</script>
<script>
(function () {
    var data = window.recipeBomData || { items: [], imports: [], units: [] };
    var tbody = document.getElementById('ingredientRows');
    var addBtn = document.getElementById('addIngredient');
    var form = document.getElementById('recipeBomForm');
    if (!tbody || !form) return;

    // to_base = how many "base" units equal 1 of this unit (g and ml are base)
    var UNIT_MAP = {
        kg: { family: 'mass', to_base: 1000 },
        kilogram: { family: 'mass', to_base: 1000 },
        kilograms: { family: 'mass', to_base: 1000 },
        g: { family: 'mass', to_base: 1 },
        gram: { family: 'mass', to_base: 1 },
        grams: { family: 'mass', to_base: 1 },
        mg: { family: 'mass', to_base: 0.001 },
        l: { family: 'volume', to_base: 1000 },
        liter: { family: 'volume', to_base: 1000 },
        litre: { family: 'volume', to_base: 1000 },
        liters: { family: 'volume', to_base: 1000 },
        litres: { family: 'volume', to_base: 1000 },
        ml: { family: 'volume', to_base: 1 },
        milliliter: { family: 'volume', to_base: 1 },
        millilitre: { family: 'volume', to_base: 1 },
        pcs: { family: 'count', to_base: 1 },
        pc: { family: 'count', to_base: 1 },
        piece: { family: 'count', to_base: 1 },
        pieces: { family: 'count', to_base: 1 },
        unit: { family: 'count', to_base: 1 },
        portion: { family: 'count', to_base: 1 },
        plate: { family: 'count', to_base: 1 },
        serving: { family: 'count', to_base: 1 },
        cup: { family: 'volume', to_base: 240 },
        tbsp: { family: 'volume', to_base: 15 },
        tsp: { family: 'volume', to_base: 5 }
    };

    // Only matching units appear in the dropdown for each ingredient type
    var FAMILY_UNITS = {
        mass: [
            { value: 'kg', label: 'kg' },
            { value: 'g', label: 'g' },
            { value: 'mg', label: 'mg' }
        ],
        volume: [
            { value: 'L', label: 'L' },
            { value: 'ml', label: 'ml' },
            { value: 'cup', label: 'cup' },
            { value: 'tbsp', label: 'tbsp' },
            { value: 'tsp', label: 'tsp' }
        ],
        count: [
            { value: 'pcs', label: 'pcs' }
        ]
    };

    function norm(u) {
        u = String(u || '').trim().toLowerCase();
        if (u === 'lt') return 'l';
        return u;
    }

    function familyOf(unit) {
        var meta = UNIT_MAP[norm(unit)];
        return meta ? meta.family : null;
    }

    function unitsForStockUnit(stockUnit) {
        var family = familyOf(stockUnit);
        if (family && FAMILY_UNITS[family]) {
            return FAMILY_UNITS[family];
        }
        // Unknown unit — only allow that exact unit
        if (stockUnit) {
            return [{ value: stockUnit, label: stockUnit }];
        }
        return [];
    }

    function fillUnitSelect(unitSelect, stockUnit, preferredUnit) {
        var options = unitsForStockUnit(stockUnit);
        var preferred = preferredUnit || '';
        var html = '<option value="">Select unit</option>';

        if (!stockUnit) {
            unitSelect.innerHTML = '<option value="">Select ingredient first</option>';
            unitSelect.disabled = true;
            return;
        }

        unitSelect.disabled = false;
        options.forEach(function (u) {
            html += '<option value="' + u.value + '">' + u.label + '</option>';
        });
        unitSelect.innerHTML = html;

        // Prefer saved recipe unit if it belongs to this family; else stock unit
        var pick = null;
        if (preferred) {
            Array.prototype.forEach.call(unitSelect.options, function (o) {
                if (o.value && norm(o.value) === norm(preferred)) pick = o.value;
            });
        }
        if (!pick && stockUnit) {
            Array.prototype.forEach.call(unitSelect.options, function (o) {
                if (o.value && norm(o.value) === norm(stockUnit)) pick = o.value;
            });
        }
        if (!pick && options.length) {
            pick = options[0].value;
        }
        if (pick) unitSelect.value = pick;
    }

    function convertQty(qty, fromUnit, toUnit) {
        var from = norm(fromUnit);
        var to = norm(toUnit);
        if (!from || !to || from === to) return { ok: true, qty: qty, converted: false };
        var a = UNIT_MAP[from];
        var b = UNIT_MAP[to];
        if (!a || !b || a.family !== b.family || !b.to_base) {
            return { ok: false, qty: qty, converted: false };
        }
        return { ok: true, qty: (qty * a.to_base) / b.to_base, converted: true };
    }

    function money(n) {
        var v = Number(n) || 0;
        return '৳ ' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtQty(n) {
        var v = Number(n) || 0;
        return v.toLocaleString(undefined, { maximumFractionDigits: 4 });
    }

    function itemOptionsHtml(selected) {
        var html = '<option value="">Select ingredient</option>';
        data.items.forEach(function (item) {
            var sel = String(selected || '') === String(item.id) ? ' selected' : '';
            html += '<option value="' + item.id + '" data-unit="' + (item.unit || '') + '" data-cost="' + item.unit_cost + '"' + sel + '>'
                + item.name + ' (' + (item.sku || '') + ')</option>';
        });
        return html;
    }

    function reindex() {
        Array.prototype.forEach.call(tbody.querySelectorAll('.bom-row'), function (row, i) {
            row.dataset.index = i;
            row.querySelector('.bom-idx').textContent = String(i + 1);
            row.querySelector('.bom-item').name = 'ingredients[' + i + '][inventory_item_id]';
            row.querySelector('.bom-qty').name = 'ingredients[' + i + '][quantity]';
            row.querySelector('.bom-unit').name = 'ingredients[' + i + '][unit]';
        });
    }

    function syncRowUnits(row, preferredUnit, useStockDefault) {
        var select = row.querySelector('.bom-item');
        var unitSelect = row.querySelector('.bom-unit');
        var opt = select.options[select.selectedIndex];
        var stockUnit = opt && opt.value ? (opt.getAttribute('data-unit') || '') : '';
        var preferred = preferredUnit;
        if (preferred == null) {
            preferred = unitSelect.getAttribute('data-preferred') || unitSelect.value || '';
        }
        if (useStockDefault) {
            preferred = stockUnit;
        }
        fillUnitSelect(unitSelect, stockUnit, preferred);
        unitSelect.removeAttribute('data-preferred');
    }

    var priceMode = 'manual'; // 'profit' | 'manual'
    var lastPerPortion = 0;

    function roundMoney(n) {
        return Math.round((Number(n) || 0) * 100) / 100;
    }

    // Profit % = margin on selling price (not markup on cost).
    // selling = cost / (1 - margin/100)
    // margin% = (selling - cost) / selling * 100
    function clampMarginPct(pct) {
        if (!isFinite(pct)) return null;
        if (pct < 0) return 0;
        if (pct >= 100) return 99.9;
        return pct;
    }

    function applyProfitToSelling(perPortion) {
        var pctEl = document.getElementById('profitPercent');
        var sellEl = document.getElementById('sellingPrice');
        var pct = clampMarginPct(parseFloat(pctEl.value));
        if (pct === null) return;
        if (String(pctEl.value) !== '' && parseFloat(pctEl.value) !== pct) {
            pctEl.value = pct.toFixed(1);
        }
        if (pct >= 100) return;
        var denom = 1 - (pct / 100);
        if (denom <= 0) return;
        var selling = roundMoney(perPortion / denom);
        sellEl.value = selling.toFixed(2);
    }

    function applySellingToProfit(perPortion) {
        var pctEl = document.getElementById('profitPercent');
        var selling = parseFloat(document.getElementById('sellingPrice').value || '0') || 0;
        if (selling > 0.0005) {
            var pct = ((selling - perPortion) / selling) * 100;
            pctEl.value = isFinite(pct) ? pct.toFixed(1) : '';
        } else if (selling <= 0) {
            pctEl.value = '';
        }
    }

    function updatePricingNote() {
        var note = document.getElementById('pricingModeNote');
        if (!note) return;
        note.textContent = priceMode === 'profit' ? 'Auto from margin %' : 'Manual price';
        note.classList.toggle('is-auto', priceMode === 'profit');
    }

    function updatePricingFormula(perPortion, selling, margin) {
        var hint = document.getElementById('pricingFormulaHint');
        if (!hint) return;
        if (priceMode === 'profit' && isFinite(margin) && margin < 100) {
            hint.textContent = money(perPortion) + ' ÷ (1 − ' + margin.toFixed(1) + '%) = ' + money(selling);
        } else if (selling > 0) {
            hint.textContent = 'Margin = profit ÷ selling = ' + money(selling - perPortion) + ' ÷ ' + money(selling);
        } else {
            hint.textContent = 'Selling = cost ÷ (1 − margin%)';
        }
    }

    function recalc() {
        var totalIngredients = 0;
        Array.prototype.forEach.call(tbody.querySelectorAll('.bom-row'), function (row) {
            var select = row.querySelector('.bom-item');
            var opt = select.options[select.selectedIndex];
            var stockUnit = opt && opt.value ? (opt.getAttribute('data-unit') || '') : '';
            var unitCost = opt && opt.value ? parseFloat(opt.getAttribute('data-cost') || '0') : 0;
            var qty = parseFloat(row.querySelector('.bom-qty').value || '0');
            qty = isFinite(qty) ? qty : 0;
            var recipeUnit = row.querySelector('.bom-unit').value || stockUnit;
            var conv = convertQty(qty, recipeUnit, stockUnit);
            var stockQty = conv.ok ? conv.qty : qty;
            var line = unitCost * stockQty;
            totalIngredients += line;

            var costLabel = stockUnit
                ? money(unitCost) + ' / ' + stockUnit
                : money(unitCost);
            row.querySelector('.bom-unit-cost').value = costLabel;
            row.querySelector('.bom-line-total').value = money(line);

            var note = row.querySelector('.bom-conv-note');
            if (note) {
                if (opt && opt.value && conv.ok && conv.converted && qty > 0) {
                    note.hidden = false;
                    note.textContent = fmtQty(qty) + ' ' + recipeUnit + ' = ' + fmtQty(stockQty) + ' ' + stockUnit;
                    note.classList.remove('warn');
                } else if (opt && opt.value && !conv.ok && recipeUnit && stockUnit && norm(recipeUnit) !== norm(stockUnit)) {
                    note.hidden = false;
                    note.textContent = 'Unit mismatch — cost uses ' + stockUnit;
                    note.classList.add('warn');
                } else {
                    note.hidden = true;
                    note.textContent = '';
                    note.classList.remove('warn');
                }
            }
        });

        var packaging = parseFloat(document.getElementById('packagingCost').value || '0') || 0;
        var other = parseFloat(document.getElementById('otherCost').value || '0') || 0;
        var yieldQty = Math.max(1, parseInt(document.getElementById('yieldQty').value || '1', 10) || 1);
        var yieldUnit = document.getElementById('yieldUnit').value || 'portion';
        var batchTotal = totalIngredients + packaging + other;
        var perPortion = batchTotal / yieldQty;
        var unitLabel = yieldUnit.charAt(0).toUpperCase() + yieldUnit.slice(1);

        // Keep selling price / profit % in sync with the active pricing mode
        if (priceMode === 'profit') {
            applyProfitToSelling(perPortion);
        } else {
            applySellingToProfit(perPortion);
        }
        lastPerPortion = perPortion;

        var selling = parseFloat(document.getElementById('sellingPrice').value || '0') || 0;
        var profitAmount = selling - perPortion;
        var margin = selling > 0 ? (profitAmount / selling) * 100 : 0;

        var costDisplay = document.getElementById('costPerPortionDisplay');
        if (costDisplay) costDisplay.value = roundMoney(perPortion).toFixed(2);

        var profitAmt = document.getElementById('profitAmountLabel');
        if (profitAmt) {
            profitAmt.textContent = money(profitAmount);
            profitAmt.classList.toggle('neg', profitAmount < 0);
        }

        document.getElementById('ingredientsTotalLabel').textContent = money(totalIngredients);
        document.getElementById('sumIngredients').textContent = money(totalIngredients);
        document.getElementById('sumPerPortion').textContent = money(perPortion);
        var sumSelling = document.getElementById('sumSelling');
        if (sumSelling) sumSelling.textContent = money(selling);
        var sumProfit = document.getElementById('sumProfit');
        if (sumProfit) {
            sumProfit.textContent = money(profitAmount);
            sumProfit.classList.toggle('neg', profitAmount < 0);
        }
        document.getElementById('previewCostPer').textContent = money(perPortion);
        document.getElementById('previewYield').textContent = yieldQty + ' ' + unitLabel + (yieldQty > 1 && !/s$/i.test(unitLabel) ? 's' : '');
        var hint = document.getElementById('previewYieldHint');
        if (hint) {
            hint.textContent = yieldQty === 1
                ? 'Ingredients below = 1 ' + yieldUnit
                : 'Ingredients make ' + yieldQty + ' ' + yieldUnit + 's · cost ÷ ' + yieldQty;
        }
        var marginEl = document.getElementById('sumMargin');
        marginEl.textContent = (isFinite(margin) ? margin.toFixed(1) : '0.0') + '%';
        marginEl.classList.toggle('neg', margin < 0);
        updatePricingFormula(perPortion, selling, margin);
        updatePricingNote();
    }

    function bindRow(row) {
        var select = row.querySelector('.bom-item');
        select.addEventListener('change', function () {
            // New ingredient → only show belonging units, default to stock unit
            syncRowUnits(row, null, true);
            recalc();
        });
        row.querySelector('.bom-qty').addEventListener('input', recalc);
        row.querySelector('.bom-unit').addEventListener('change', recalc);
        row.querySelector('.bom-remove').addEventListener('click', function () {
            if (tbody.querySelectorAll('.bom-row').length <= 1) {
                select.value = '';
                row.querySelector('.bom-qty').value = '';
                syncRowUnits(row, '', false);
                recalc();
                return;
            }
            row.remove();
            reindex();
            recalc();
        });
    }

    function addRow(preset) {
        var i = tbody.querySelectorAll('.bom-row').length;
        var tr = document.createElement('tr');
        tr.className = 'bom-row';
        tr.dataset.index = String(i);
        tr.innerHTML =
            '<td class="bom-idx">' + (i + 1) + '</td>' +
            '<td><select class="field bom-item" name="ingredients[' + i + '][inventory_item_id]" required>' + itemOptionsHtml(preset && preset.inventory_item_id) + '</select></td>' +
            '<td><input class="field bom-unit-cost" type="text" value="" readonly tabindex="-1"><small class="bom-conv-note" hidden></small></td>' +
            '<td><input class="field bom-qty" type="number" min="0.001" step="0.001" name="ingredients[' + i + '][quantity]" value="' + (preset && preset.quantity != null ? preset.quantity : '') + '" placeholder="0.00" required></td>' +
            '<td><select class="field bom-unit" name="ingredients[' + i + '][unit]"><option value="">Select ingredient first</option></select></td>' +
            '<td><input class="field bom-line-total" type="text" value="৳ 0.00" readonly tabindex="-1"></td>' +
            '<td><button type="button" class="bom-remove" title="Remove ingredient" aria-label="Remove"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg></button></td>';
        tbody.appendChild(tr);
        bindRow(tr);
        if (preset && preset.inventory_item_id) {
            if (preset.quantity != null) tr.querySelector('.bom-qty').value = preset.quantity;
            syncRowUnits(tr, preset.unit || null, !preset.unit);
        } else {
            syncRowUnits(tr, '', false);
        }
        recalc();
    }

    Array.prototype.forEach.call(tbody.querySelectorAll('.bom-row'), function (row) {
        bindRow(row);
        // Keep saved unit if compatible; otherwise fall back to stock unit
        syncRowUnits(row, null, false);
    });
    addBtn.addEventListener('click', function () { addRow(null); });

    ['yieldQty', 'yieldUnit', 'packagingCost', 'otherCost'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', recalc);
            el.addEventListener('change', recalc);
        }
    });

    var profitEl = document.getElementById('profitPercent');
    var sellingEl = document.getElementById('sellingPrice');
    if (profitEl) {
        profitEl.addEventListener('input', function () {
            priceMode = 'profit';
            applyProfitToSelling(lastPerPortion || 0);
            recalc();
        });
        profitEl.addEventListener('change', function () {
            priceMode = 'profit';
            applyProfitToSelling(lastPerPortion || 0);
            recalc();
        });
    }
    if (sellingEl) {
        sellingEl.addEventListener('input', function () {
            priceMode = 'manual';
            applySellingToProfit(lastPerPortion || 0);
            recalc();
        });
        sellingEl.addEventListener('change', function () {
            priceMode = 'manual';
            applySellingToProfit(lastPerPortion || 0);
            recalc();
        });
    }

    document.getElementById('importRecipeBtn').addEventListener('click', function () {
        var id = document.getElementById('importRecipeSelect').value;
        if (!id) return;
        var recipe = (data.imports || []).find(function (r) { return String(r.id) === String(id); });
        if (!recipe || !recipe.ingredients || !recipe.ingredients.length) {
            alert('That recipe has no ingredients to import.');
            return;
        }
        tbody.innerHTML = '';
        recipe.ingredients.forEach(function (ing) { addRow(ing); });
        reindex();
        recalc();
    });

    document.getElementById('saveDraftBtn').addEventListener('click', function () {
        document.getElementById('formAction').value = 'draft';
        document.getElementById('recipeStatus').value = 'draft';
    });
    document.getElementById('saveRecipeBtn').addEventListener('click', function () {
        document.getElementById('formAction').value = 'save';
        if (document.getElementById('recipeStatus').value === 'draft') {
            document.getElementById('recipeStatus').value = 'active';
        }
    });

    document.getElementById('resetForm').addEventListener('click', function () {
        setTimeout(function () {
            Array.prototype.forEach.call(tbody.querySelectorAll('.bom-row'), function (row) {
                syncRowUnits(row, null, false);
            });
            recalc();
        }, 0);
    });

    recalc();
})();

(function () {
    var form = document.getElementById('recipeBomForm');
    if (!form) return;

    var nameInput = document.getElementById('recipeName');
    var checkUrl = form.dataset.checkNameUrl;
    var duplicateConfirmed = false;

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            duplicateConfirmed = false;
        });
    }

    form.addEventListener('submit', function (e) {
        if (duplicateConfirmed || !checkUrl) return;

        var name = nameInput ? nameInput.value.trim() : '';
        if (!name) return;

        e.preventDefault();

        var exceptId = form.dataset.recipeId || '';
        var url = checkUrl + '?name=' + encodeURIComponent(name);
        if (exceptId) url += '&except=' + encodeURIComponent(exceptId);

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (res) { return res.json(); })
            .then(function (payload) {
                if (!payload || !payload.exists) {
                    duplicateConfirmed = true;
                    form.submit();
                    return;
                }

                var codes = (payload.recipes || [])
                    .map(function (r) { return r.code; })
                    .filter(Boolean)
                    .join(', ');
                var message = 'A recipe named "' + name + '" already exists';
                if (codes) message += ' (' + codes + ')';
                message += '.\n\nSave anyway?';

                if (window.confirm(message)) {
                    duplicateConfirmed = true;
                    form.submit();
                }
            })
            .catch(function () {
                duplicateConfirmed = true;
                form.submit();
            });
    });
})();

(function () {
    var tip = document.getElementById('yieldExamplePanel');
    var btn = document.getElementById('yieldHintToggle');
    if (!tip || !btn) return;
    var openByDefault = formModeOpen();
    if (openByDefault) {
        tip.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
    }
    btn.addEventListener('click', function () {
        var open = tip.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    function formModeOpen() {
        var form = document.getElementById('recipeBomForm');
        return form && form.dataset.mode === 'create';
    }
})();
</script>
@endpush
