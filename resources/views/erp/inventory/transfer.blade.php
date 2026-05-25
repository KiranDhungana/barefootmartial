@extends('layouts.admin')

@section('title', 'Transfer stock')
@section('page_title', 'Branch stock transfer')

@section('content')
    <div class="panel-card mx-auto" style="max-width:520px">
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.inventory.transfer.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">From branch</label>
                    <select name="from_branch_id" class="form-select rounded-3" required>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">To branch</label>
                    <select name="to_branch_id" class="form-select rounded-3" required>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Item</label>
                    <select name="inventory_item_id" class="form-select rounded-3" required>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" min="1" name="quantity" class="form-control rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-control rounded-3"></textarea>
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Transfer</button>
                <a href="{{ route('erp.inventory.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
