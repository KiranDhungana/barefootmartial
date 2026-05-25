@php $b = $branch ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control rounded-3" value="{{ old('name', $b?->name) }}" required></div>
    <div class="col-md-6"><label class="form-label">Code</label><input name="code" class="form-control rounded-3" value="{{ old('code', $b?->code) }}" required></div>
    <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control rounded-3" rows="2">{{ old('address', $b?->address) }}</textarea></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control rounded-3" value="{{ old('phone', $b?->phone) }}"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control rounded-3" value="{{ old('email', $b?->email) }}"></div>
    <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $b?->is_active ?? true))><label class="form-check-label">Active on public site</label></div></div>
</div>
