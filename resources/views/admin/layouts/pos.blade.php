<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — Bynnas Restora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    @php
        $posRoot = rtrim(request()->root(), '/');
        $posCssPath = public_path('css/pos.css');
        $posCssVer = file_exists($posCssPath) ? filemtime($posCssPath) : time();
    @endphp
    {{-- Critical layout: works even if external CSS fails --}}
    <style>
        html,body{width:100%;height:100%;margin:0;overflow:hidden}
        .pos-shell{position:fixed;inset:0;width:100%;height:100%;height:100dvh;display:grid;grid-template-rows:64px minmax(0,1fr) auto;overflow:hidden;direction:ltr;background:#eef1f6}
        .pos-body{display:grid!important;grid-template-columns:minmax(0,1fr) 420px!important;grid-template-areas:'catalog checkout'!important;gap:14px;padding:14px;min-height:0;overflow:hidden;direction:ltr}
        .catalog-panel{grid-area:catalog!important;min-height:0;display:flex;flex-direction:column;overflow:hidden}
        .checkout-panel{grid-area:checkout!important;min-height:0;display:flex;flex-direction:column;overflow:hidden}
        .product-grid{flex:1;min-height:0;display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));grid-auto-rows:min-content;align-content:start;align-items:start;gap:14px;overflow:auto;padding:14px}
        .product-card{display:flex;flex-direction:column;background:#fff;border:1px solid #e4e9f0;border-radius:14px;overflow:hidden;min-height:250px;cursor:pointer}
        .product-card .thumb{height:148px;flex:0 0 148px;background:#f1f5f9;position:relative}
        .product-card .thumb img{width:100%;height:100%;object-fit:cover;display:block}
        .product-card .info{padding:10px 12px 12px;background:#fff;min-height:78px}
        .product-card .name{margin:0 0 8px;font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .product-card .foot{display:flex;align-items:center;justify-content:space-between}
        .product-card .price{font-weight:800}
        .product-card .add-btn{width:32px;height:32px;border-radius:999px;background:#f28c28;color:#fff;display:grid;place-items:center;font-size:22px}
    </style>
    <link rel="stylesheet" href="{{ $posRoot }}/css/pos.css?v={{ $posCssVer }}">
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
