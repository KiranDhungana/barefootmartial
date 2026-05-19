@extends('layouts.admin')

@section('title', 'Update player')
@section('page_title', 'Edit player')
@section('page_subtitle', 'Update profile and documents')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Player details</div>
        <div class="panel-body p-4">
            <div class="mx-auto" style="max-width: 640px;">
                <form action="{{ url('admin-update/' . $updateinfo->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input value="{{ $updateinfo->name }}" name="name" type="text"
                            class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input value="{{ $updateinfo->address }}" name="address" type="text"
                            class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age</label>
                        <input name="age" value="{{ $updateinfo->age }}" type="number"
                            class="form-control rounded-3 @error('age') is-invalid @enderror">
                        @error('age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input name="number" value="{{ $updateinfo->phone }}" type="tel"
                            class="form-control rounded-3 @error('number') is-invalid @enderror">
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rank</label>
                        <input name="rank" value="{{ $updateinfo->rank }}" type="number" min="0" step="1"
                            class="form-control rounded-3 @error('rank') is-invalid @enderror">
                        @error('rank')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea rows="5" name="description"
                            class="form-control rounded-3 @error('description') is-invalid @enderror">{{ $updateinfo->description }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Upload documents</label>
                        <input multiple class="form-control rounded-3" name="file[]" type="file">
                    </div>
                    <button type="submit" class="btn btn-admin-primary text-white px-4 rounded-pill">Save changes</button>
                </form>
            </div>
        </div>
    </div>
@endsection
