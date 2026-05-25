@php $s = $schedule ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Branch</label><select name="branch_id" class="form-select rounded-3" required>@foreach($branches as $b)<option value="{{ $b->id }}" @selected(old('branch_id', $s?->branch_id)==$b->id)>{{ $b->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Class name</label><input name="name" class="form-control rounded-3" value="{{ old('name', $s?->name) }}" required></div>
    <div class="col-md-4"><label class="form-label">Day</label><select name="day_of_week" class="form-select rounded-3">@foreach($days as $d)<option value="{{ $d }}" @selected(old('day_of_week', $s?->day_of_week)==$d)>{{ ucfirst($d) }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Start</label><input type="time" name="start_time" class="form-control rounded-3" value="{{ old('start_time', $s?->start_time ? \Carbon\Carbon::parse($s->start_time)->format('H:i') : '') }}" required></div>
    <div class="col-md-4"><label class="form-label">End</label><input type="time" name="end_time" class="form-control rounded-3" value="{{ old('end_time', $s?->end_time ? \Carbon\Carbon::parse($s->end_time)->format('H:i') : '') }}"></div>
    <div class="col-md-6"><label class="form-label">Coach</label><input name="coach_name" class="form-control rounded-3" value="{{ old('coach_name', $s?->coach_name) }}"></div>
    <div class="col-md-6"><label class="form-label">Belt level</label><select name="belt_level" class="form-select rounded-3"><option value="">Any</option>@foreach($belts as $belt)<option value="{{ $belt }}" @selected(old('belt_level', $s?->belt_level)==$belt)>{{ $belt }}</option>@endforeach</select></div>
</div>
