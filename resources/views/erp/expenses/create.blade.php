@extends('layouts.admin')

@section('title', 'Add expense')
@section('page_title', 'Add expense')

@section('content')
    <div class="panel-card mx-auto" style="max-width:520px">
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.expenses.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select rounded-3" required>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id', auth()->user()->branch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select rounded-3" required>
                        @foreach ($categories as $c)
                            <option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control rounded-3" required
                        value="{{ old('amount') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="expense_date" class="form-control rounded-3" required
                        value="{{ old('expense_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control rounded-3" value="{{ old('description') }}">
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Save</button>
                <a href="{{ route('erp.expenses.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
