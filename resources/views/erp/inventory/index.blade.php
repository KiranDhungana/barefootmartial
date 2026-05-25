@extends('layouts.admin')

@section('title', 'Inventory')
@section('page_title', 'Inventory')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        @if ($branches->count() > 1)
            <form method="get" class="d-flex gap-2">
                <select name="branch_id" class="form-select rounded-3" onchange="this.form.submit()">
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('erp.inventory.transfer') }}" class="btn btn-outline-primary rounded-pill">Transfer stock</a>
    </div>

    @if (count($lowStock) > 0)
        <div class="alert alert-warning border-0 rounded-4 mb-3">
            <strong>Low stock:</strong>
            @foreach ($lowStock as $alert)
                {{ $alert['item']->name }} ({{ $alert['quantity'] }} left)@if (! $loop->last), @endif
            @endforeach
        </div>
    @endif

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Set stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php $qty = $stocks->get($item->id)?->quantity ?? 0; @endphp
                        <tr class="{{ $qty <= $item->low_stock_threshold ? 'table-warning' : '' }}">
                            <td>{{ $item->name }} <span class="text-muted small">({{ $item->code }})</span></td>
                            <td>{{ ucfirst($item->category) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td class="fw-semibold">{{ $qty }}</td>
                            <td>
                                <form method="post" action="{{ route('erp.inventory.stock') }}" class="d-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                                    <input type="hidden" name="inventory_item_id" value="{{ $item->id }}">
                                    <input type="number" min="0" name="quantity" class="form-control form-control-sm"
                                        style="width:80px" value="{{ $qty }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
