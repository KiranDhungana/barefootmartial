@php $e = $event ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Branch</label><select name="branch_id" class="form-select rounded-3"><option value="">All branches</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected(old('branch_id', $e?->branch_id)==$b->id)>{{ $b->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control rounded-3" value="{{ old('title', $e?->title) }}" required></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $e?->description) }}</textarea></div>
    <div class="col-md-4"><label class="form-label">Event date</label><input type="date" name="event_date" class="form-control rounded-3" value="{{ old('event_date', $e?->event_date?->format('Y-m-d')) }}"></div>
    <div class="col-md-4"><label class="form-label">Registration deadline</label><input type="date" name="registration_deadline" class="form-control rounded-3" value="{{ old('registration_deadline', $e?->registration_deadline?->format('Y-m-d')) }}"></div>
    <div class="col-md-4"><label class="form-label">Fee</label><input type="number" step="0.01" name="fee_amount" class="form-control rounded-3" value="{{ old('fee_amount', $e?->fee_amount ?? 0) }}"></div>
    <div class="col-12"><div class="form-check"><input type="checkbox" name="is_published" value="1" class="form-check-input" @checked(old('is_published', $e?->is_published ?? true))><label class="form-check-label">Published on public site</label></div></div>
</div>
