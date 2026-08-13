@extends('admin.layouts.app')

@section('title', 'QR · Table '.$table->code)

@section('content')
<div class="page-head">
    <div>
        <h1>Table {{ $table->code }} QR</h1>
        <p>Guests scan this code to open the live menu for this table.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.tables.index') }}" class="btn">Back</a>
        <button type="button" class="btn btn-gold" onclick="window.print()">Print</button>
    </div>
</div>

@if (session('success'))
    <div class="alert success" style="margin-bottom:12px">{{ session('success') }}</div>
@endif

<section class="card" style="max-width:520px">
    <div style="text-align:center;padding:18px 12px">
        <div class="eyebrow" style="letter-spacing:.08em;text-transform:uppercase;font-size:11px;color:#78716c;font-weight:700">QR Table Order</div>
        <h2 style="margin:8px 0 4px;font-size:28px">Table {{ $table->code }}</h2>
        <p style="margin:0 0 16px;color:#78716c">{{ $table->zone }} · {{ $table->capacity }} seats</p>
        <img
            src="{{ $table->qrImageUrl(320) }}"
            alt="QR for table {{ $table->code }}"
            width="320"
            height="320"
            style="border:1px solid rgba(28,25,23,.1);border-radius:16px;background:#fff"
        >
        <p style="margin:14px 0 0;font-size:12px;color:#78716c;word-break:break-all">{{ $table->qrOrderUrl() }}</p>
    </div>

    <div class="form-actions" style="justify-content:center;gap:8px;flex-wrap:wrap">
        <a class="btn" href="{{ $table->qrOrderUrl() }}" target="_blank" rel="noopener">Open guest page</a>
        <form method="POST" action="{{ route('admin.tables.qr.refresh', $table) }}" onsubmit="return confirm('Regenerate QR? Old printed codes will stop working.')">
            @csrf
            <button class="btn" type="submit">Regenerate QR</button>
        </form>
    </div>
</section>

<style>
@media print {
    .sidebar, .topbar, .page-head-actions, .form-actions, .menu-btn { display: none !important; }
    .main { margin: 0 !important; padding: 0 !important; }
    .card { box-shadow: none !important; border: 0 !important; }
}
</style>
@endsection
