@extends('layouts.admin')

@section('title', 'Audit log')
@section('page_title', 'Audit trail')
@section('page_subtitle', 'Who changed what and when')

@section('content')
    <form method="get" class="mb-3 d-flex gap-2" style="max-width:360px">
        <input type="text" name="action" value="{{ request('action') }}" class="form-control rounded-3"
            placeholder="Filter action…">
        <button type="submit" class="btn btn-outline-secondary rounded-3">Filter</button>
    </form>

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0 small">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Record</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>
                                @if ($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($log->new_values)
                                    <span class="text-muted">{{ Str::limit(json_encode($log->new_values), 80) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No audit entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
