@extends('layouts.admin')

@section('title', 'Delete notices')
@section('page_title', 'Delete notices')
@section('page_subtitle', 'Remove notices from the site')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Published notices</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">S.No</th>
                        <th scope="col">Uploaded</th>
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($notice as $id)
                        <tr>
                            <th scope="row" class="text-muted fw-semibold">{{ $i }}</th>
                            <td>{{ $id->created_at }}</td>
                            <td class="fw-medium">{{ $id->title }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($id->description, 80) }}</td>
                            <td class="text-end">
                                <form action="/delete-notice/{{ $id->id }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @php
                            $i = $i + 1;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
