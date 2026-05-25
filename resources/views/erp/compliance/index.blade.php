@extends('layouts.admin')
@section('title', 'Compliance')
@section('page_title', 'Branch compliance scores')
@section('content')
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Branch</th><th>Registration</th><th>Uniform</th><th>Reporting</th><th>Overall</th></tr></thead>
            <tbody>
                @foreach ($scores as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row['branch']->name }}</td>
                        <td>{{ $row['registration'] }}%</td>
                        <td>{{ $row['uniform'] }}%</td>
                        <td>{{ $row['reporting'] }}%</td>
                        <td><strong>{{ $row['overall'] }}%</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
