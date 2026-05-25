@extends('layouts.admin')

@section('title', 'Expenses')
@section('page_title', 'Branch expenses')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form method="get" class="d-flex flex-wrap gap-2">
            @if ($branches->isNotEmpty())
                <select name="branch_id" class="form-select rounded-3" onchange="this.form.submit()">
                    <option value="">All branches</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif
            <input type="month" name="month" value="{{ request('month') }}" class="form-control rounded-3"
                onchange="this.form.submit()">
        </form>
        <a href="{{ route('erp.expenses.create') }}" class="btn btn-admin-primary text-white">Add expense</a>
    </div>

    <p class="text-muted mb-3">Filtered total: <strong>{{ number_format($total, 2) }}</strong></p>

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $e)
                        <tr>
                            <td>{{ $e->expense_date->format('M j, Y') }}</td>
                            <td>{{ $e->branch->name }}</td>
                            <td>{{ $e->categoryLabel() }}</td>
                            <td>{{ $e->description ?? '—' }}</td>
                            <td class="text-end">{{ number_format($e->amount, 2) }}</td>
                            <td class="text-end">
                                <form method="post" action="{{ route('erp.expenses.destroy', $e) }}"
                                    onsubmit="return confirm('Delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">No expenses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $expenses->links() }}
@endsection
