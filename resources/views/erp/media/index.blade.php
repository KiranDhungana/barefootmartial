@extends('layouts.admin')

@section('title', 'Gallery & slider')
@section('page_title', 'Gallery & slider')
@section('page_subtitle', 'Upload images to Cloudinary for the home slider and gallery page')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-pills gap-1 mb-3">
        @foreach ($types as $key => $label)
            <li class="nav-item">
                <a class="nav-link rounded-pill {{ $type === $key ? 'active' : '' }}"
                    href="{{ route('erp.media.index', ['type' => $key]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-heading">Upload {{ $types[$type] ?? 'image' }}</div>
                <div class="panel-body p-4">
                    <form method="post" action="{{ route('erp.media.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="mb-3">
                            <label class="form-label">Image <span class="text-danger">*</span></label>
                            <input type="file" name="image" accept="image/*" class="form-control rounded-3" required>
                            <div class="form-text">JPEG, PNG, WebP or GIF — max 8 MB. Stored on Cloudinary.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}"
                                placeholder="{{ $type === 'slider' ? 'Slide headline' : 'Optional caption' }}">
                        </div>
                        @if ($type === 'slider')
                            <div class="mb-3">
                                <label class="form-label">Subtitle</label>
                                <textarea name="subtitle" rows="2" class="form-control rounded-3"
                                    placeholder="Short supporting line">{{ old('subtitle') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Button label</label>
                                <input type="text" name="cta_label" class="form-control rounded-3"
                                    value="{{ old('cta_label', 'Register as student') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Button URL</label>
                                <input type="text" name="cta_url" class="form-control rounded-3"
                                    value="{{ old('cta_url', route('public.register')) }}">
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Sort order</label>
                            <input type="number" min="0" name="sort_order" class="form-control rounded-3"
                                value="{{ old('sort_order', 0) }}">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                                checked>
                            <label class="form-check-label" for="is_active">Active / visible on site</label>
                        </div>
                        <button type="submit" class="btn btn-admin-primary text-white w-100">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload to Cloudinary
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-heading">{{ $types[$type] ?? 'Media' }} ({{ $items->total() }})</div>
                <div class="panel-body p-3">
                    @if ($items->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No images yet. Upload one to get started.</p>
                    @else
                        <div class="row g-3">
                            @foreach ($items as $item)
                                <div class="col-md-6">
                                    <div class="border rounded-4 overflow-hidden h-100">
                                        <img src="{{ $item->url }}" alt="{{ $item->title ?: 'Media' }}"
                                            class="w-100" style="height:160px;object-fit:cover;">
                                        <div class="p-3">
                                            <form method="post" action="{{ route('erp.media.update', $item) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-2">
                                                    <input type="text" name="title" class="form-control form-control-sm rounded-3"
                                                        value="{{ $item->title }}" placeholder="Title">
                                                </div>
                                                @if ($type === 'slider')
                                                    <div class="mb-2">
                                                        <textarea name="subtitle" rows="2"
                                                            class="form-control form-control-sm rounded-3"
                                                            placeholder="Subtitle">{{ $item->subtitle }}</textarea>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-6">
                                                            <input type="text" name="cta_label"
                                                                class="form-control form-control-sm rounded-3"
                                                                value="{{ $item->cta_label }}" placeholder="Button">
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="cta_url"
                                                                class="form-control form-control-sm rounded-3"
                                                                value="{{ $item->cta_url }}" placeholder="URL">
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <input type="number" min="0" name="sort_order"
                                                            class="form-control form-control-sm rounded-3"
                                                            style="width:70px" value="{{ $item->sort_order }}">
                                                        <div class="form-check mb-0">
                                                            <input type="checkbox" name="is_active" value="1"
                                                                class="form-check-input" id="active-{{ $item->id }}"
                                                                @checked($item->is_active)>
                                                            <label class="form-check-label small"
                                                                for="active-{{ $item->id }}">Active</label>
                                                        </div>
                                                    </div>
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-primary rounded-pill">Save</button>
                                                </div>
                                            </form>
                                            <form method="post" action="{{ route('erp.media.destroy', $item) }}"
                                                class="mt-2"
                                                onsubmit="return confirm('Delete this image from the site and Cloudinary?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger rounded-pill w-100">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $items->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
