@extends('admin.layouts.app')

@section('title', 'Menu Items')

@section('content')
<div class="page-head">
    <div>
        <h1>Menu Items</h1>
        <p>Manage dishes shown on POS and the customer website.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.menu-items.create') }}" class="btn btn-gold">+ Add Item</a>
    </div>
</div>

@if (session('success'))
    <div class="flash success">{{ session('success') }}</div>
@endif

<section class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Badge</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menuItems as $item)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover">
                                @endif
                                <div>
                                    <strong>{{ $item->name }}</strong>
                                    @if ($item->is_favorite)
                                        <span class="pill amber" style="margin-left:6px">★</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->category ?? '—' }}</td>
                        <td>৳ {{ number_format((float) $item->price, 2) }}</td>
                        <td>{{ $item->badge ?? ($item->is_bestseller ? 'Bestseller' : '—') }}</td>
                        <td>
                            <span class="pill {{ $item->is_available ? 'green' : 'slate' }}">
                                {{ $item->is_available ? 'Available' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.menu-items.edit', $item) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}" onsubmit="return confirm('Delete this menu item?')">
                                @csrf @method('DELETE')
                                <button class="btn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No menu items yet. Add your first dish.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
