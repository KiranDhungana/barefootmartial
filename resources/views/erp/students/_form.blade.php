@php
    $s = $student ?? null;
@endphp
<div class="mb-3">
    <label class="form-label">Branch @if(in_array('branch_id', config('academy.official_required_fields', [])))<span class="text-danger">*</span>@endif</label>
    <select name="branch_id" class="form-select rounded-3 @error('branch_id') is-invalid @enderror"
        @if(auth()->user()?->isBranchScoped()) disabled @endif>
        <option value="">—</option>
        @foreach ($branches as $b)
            <option value="{{ $b->id }}" @selected(old('branch_id', $s?->branch_id) == $b->id)>{{ $b->name }}</option>
        @endforeach
    </select>
    @if(auth()->user()?->isBranchScoped())
        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif
    @error('branch_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="row g-3">
    <div class="col-md-8 mb-3">
        <label class="form-label">Full name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
            value="{{ old('name', $s?->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select rounded-3">
            @foreach ($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $s?->status ?? 'active') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4 mb-3">
        <label class="form-label">Date of birth</label>
        <input type="date" name="dob" class="form-control rounded-3" value="{{ old('dob', optional($s?->dob)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select rounded-3">
            <option value="">—</option>
            @foreach (['male', 'female', 'other'] as $g)
                <option value="{{ $g }}" @selected(old('gender', $s?->gender) === $g)>{{ ucfirst($g) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Blood group</label>
        <input type="text" name="blood_group" class="form-control rounded-3" value="{{ old('blood_group', $s?->blood_group) }}"
            placeholder="e.g. O+">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $s?->phone) }}">
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address', $s?->address) }}</textarea>
</div>
<div class="panel-card mb-3">
    <div class="panel-heading py-2 px-3 small">Parent / emergency</div>
    <div class="panel-body p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Parent name</label>
                <input type="text" name="parent_name" class="form-control rounded-3"
                    value="{{ old('parent_name', $s?->parent_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Parent contact</label>
                <input type="text" name="parent_contact" class="form-control rounded-3"
                    value="{{ old('parent_contact', $s?->parent_contact) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Emergency contact</label>
                <input type="text" name="emergency_contact" class="form-control rounded-3"
                    value="{{ old('emergency_contact', $s?->emergency_contact) }}">
            </div>
        </div>
    </div>
</div>
<div class="panel-card mb-3">
    <div class="panel-heading py-2 px-3 small">Training</div>
    <div class="panel-body p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Join date</label>
                <input type="date" name="join_date" class="form-control rounded-3"
                    value="{{ old('join_date', optional($s?->join_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Belt rank</label>
                <input type="text" name="belt_rank" class="form-control rounded-3"
                    value="{{ old('belt_rank', $s?->belt_rank) }}" placeholder="e.g. Yellow">
            </div>
            <div class="col-md-4">
                <label class="form-label">Coach</label>
                <input type="text" name="coach_name" class="form-control rounded-3"
                    value="{{ old('coach_name', $s?->coach_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Batch timing</label>
                <input type="text" name="batch_timing" class="form-control rounded-3"
                    value="{{ old('batch_timing', $s?->batch_timing) }}" placeholder="e.g. Mon/Wed 5–6 PM">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fee status</label>
                <input type="text" name="fee_status" class="form-control rounded-3"
                    value="{{ old('fee_status', $s?->fee_status) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Uniform status</label>
                <input type="text" name="uniform_status" class="form-control rounded-3"
                    value="{{ old('uniform_status', $s?->uniform_status) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Discount %</label>
                <input type="number" step="0.01" min="0" max="100" name="discount_percent"
                    class="form-control rounded-3" value="{{ old('discount_percent', $s?->discount_percent ?? 0) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Scholarship type</label>
                <select name="scholarship_type" class="form-select rounded-3">
                    <option value="">—</option>
                    @foreach (config('academy.scholarship_types', []) as $stype)
                        <option value="{{ $stype }}" @selected(old('scholarship_type', $s?->scholarship_type) === $stype)>
                            {{ ucfirst(str_replace('_', ' ', $stype)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Scholarship notes</label>
                <input type="text" name="scholarship_notes" class="form-control rounded-3"
                    value="{{ old('scholarship_notes', $s?->scholarship_notes) }}">
            </div>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Photo</label>
    @if ($s?->photo_path)
        <div class="mb-2">
            <img src="{{ asset('storage/'.$s->photo_path) }}" alt="" class="rounded-3" style="max-height:120px">
        </div>
    @endif
    <input type="file" name="photo" accept="image/*" class="form-control rounded-3">
</div>
<div class="mb-4">
    <label class="form-label">Notes</label>
    <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes', $s?->notes) }}</textarea>
</div>
