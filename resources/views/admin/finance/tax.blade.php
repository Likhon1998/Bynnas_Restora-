@extends('admin.layouts.app')

@section('title', 'Tax Management')

@section('content')
<div class="page-head">
    <div>
        <h1>Tax Management</h1>
        <p>Configure VAT &amp; service rates used by POS checkout, then review collected tax from paid orders.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:0.9fr 1.1fr;gap:16px;margin-bottom:16px">
    <section class="card form-card">
        <h2 style="font-size:1.05rem;margin:0 0 12px">Active tax settings</h2>
        <form method="POST" action="{{ route('admin.tax.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>Tax name<input class="field" name="tax_name" value="{{ old('tax_name', $settings->tax_name) }}" required></label>
                <label>VAT rate (%)<input class="field" type="number" step="0.01" min="0" max="100" name="vat_rate" value="{{ old('vat_rate', $settings->vat_rate) }}" required></label>
                <label>Service charge (%)<input class="field" type="number" step="0.01" min="0" max="100" name="service_charge_rate" value="{{ old('service_charge_rate', $settings->service_charge_rate) }}" required></label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:28px">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $settings->is_active))>
                    Active on POS
                </label>
                <label class="span-2">Notes<textarea class="field" name="notes" rows="3">{{ old('notes', $settings->notes) }}</textarea></label>
            </div>
            <div class="form-actions"><button class="btn btn-gold" type="submit">Save Tax Settings</button></div>
        </form>
    </section>

    <section style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-content:start">
        <div class="card" style="padding:16px">
            <small class="muted">Taxable subtotal</small>
            <strong style="display:block;font-size:1.2rem;margin-top:6px">৳ {{ number_format($totals['taxable'], 2) }}</strong>
        </div>
        <div class="card" style="padding:16px">
            <small class="muted">{{ $settings->tax_name }} collected</small>
            <strong style="display:block;font-size:1.2rem;margin-top:6px">৳ {{ number_format($totals['tax'], 2) }}</strong>
        </div>
        <div class="card" style="padding:16px">
            <small class="muted">Service charge collected</small>
            <strong style="display:block;font-size:1.2rem;margin-top:6px">৳ {{ number_format($totals['service'], 2) }}</strong>
        </div>
        <div class="card" style="padding:16px">
            <small class="muted">Paid orders</small>
            <strong style="display:block;font-size:1.2rem;margin-top:6px">{{ number_format($totals['orders']) }}</strong>
        </div>
    </section>
</div>

<section class="card">
    <form class="toolbar" method="GET">
        <label>From <input class="field" type="date" name="from" value="{{ $filters['from'] }}"></label>
        <label>To <input class="field" type="date" name="to" value="{{ $filters['to'] }}"></label>
        <button class="btn btn-solid" type="submit">Apply</button>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Taxable</th>
                    <th>{{ $settings->tax_name }}</th>
                    <th>Service</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taxRows as $row)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('M d, Y') }}</td>
                        <td>{{ $row->orders_count }}</td>
                        <td>৳ {{ number_format((float) $row->taxable, 2) }}</td>
                        <td>৳ {{ number_format((float) $row->tax, 2) }}</td>
                        <td>৳ {{ number_format((float) $row->service, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No tax collected in this range. Process paid POS orders to populate this report.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">{{ $taxRows->links('pagination.admin') }}</div>
</section>
@endsection
