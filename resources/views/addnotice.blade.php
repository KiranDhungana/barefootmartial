@extends('layouts.admin')

@section('title', 'Add notice')
@section('page_title', 'Add notice')
@section('page_subtitle', 'Create a new notice with optional attachments')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Notice details</div>
        <div class="panel-body p-4">
            <div class="mx-auto" style="max-width: 640px;">
                <form action="{{ route('store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror"
                            placeholder="Title" value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea rows="5" name="des" class="form-control rounded-3"
                            placeholder="Description">{{ old('des') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Attachments <span class="text-danger">*</span></label>
                        <input multiple class="form-control rounded-3 @error('file') is-invalid @enderror" name="file[]"
                            type="file">
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-admin-primary text-white px-4 rounded-pill">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
