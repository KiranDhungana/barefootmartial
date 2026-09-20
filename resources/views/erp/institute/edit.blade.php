@extends('layouts.admin')

@section('title', 'Institute profile')
@section('page_title', 'Institute profile')
@section('page_subtitle', 'Location and contact used on invoices, bills, and receipts')

@section('content')
    <form method="post" action="{{ route('erp.institute.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-heading">Branding</div>
                    <div class="panel-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Legal / full name</label>
                                <input type="text" name="legal_name" class="form-control rounded-3"
                                    value="{{ old('legal_name', $profile->legal_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand line 1</label>
                                <input type="text" name="brand_line1" class="form-control rounded-3"
                                    value="{{ old('brand_line1', $profile->brand_line1) }}" placeholder="BAREFOOT">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand line 2</label>
                                <input type="text" name="brand_line2" class="form-control rounded-3"
                                    value="{{ old('brand_line2', $profile->brand_line2) }}" placeholder="MARTIAL ARTS ACADEMY">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tagline</label>
                                <input type="text" name="tagline" class="form-control rounded-3"
                                    value="{{ old('tagline', $profile->tagline) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Footer motto</label>
                                <input type="text" name="footer_tagline" class="form-control rounded-3"
                                    value="{{ old('footer_tagline', $profile->footer_tagline) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Authorized signatory</label>
                                <input type="text" name="authorized_signatory" class="form-control rounded-3"
                                    value="{{ old('authorized_signatory', $profile->authorized_signatory) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-heading">Location</div>
                    <div class="panel-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Street / area address</label>
                                <input type="text" name="address" class="form-control rounded-3"
                                    value="{{ old('address', $profile->address) }}"
                                    placeholder="e.g. Ramgram-05, Parasi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control rounded-3"
                                    value="{{ old('city', $profile->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <input type="text" name="district" class="form-control rounded-3"
                                    value="{{ old('district', $profile->district) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Province</label>
                                <input type="text" name="province" class="form-control rounded-3"
                                    value="{{ old('province', $profile->province) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control rounded-3"
                                    value="{{ old('country', $profile->country ?: 'Nepal') }}">
                            </div>
                        </div>
                        <p class="small text-muted mb-0 mt-3">
                            Preview on documents:
                            <strong>{{ $profile->locationLine() ?: '—' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-heading">Contact & tax</div>
                    <div class="panel-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control rounded-3"
                                    value="{{ old('phone', $profile->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control rounded-3"
                                    value="{{ old('email', $profile->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="text" name="website" class="form-control rounded-3"
                                    value="{{ old('website', $profile->website) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PAN</label>
                                <input type="text" name="pan" class="form-control rounded-3"
                                    value="{{ old('pan', $profile->pan) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">VAT</label>
                                <input type="text" name="vat" class="form-control rounded-3"
                                    value="{{ old('vat', $profile->vat) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-heading">Fonepay / payments</div>
                    <div class="panel-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Terminal ID</label>
                                <input type="text" name="fonepay_terminal" class="form-control rounded-3"
                                    value="{{ old('fonepay_terminal', $profile->fonepay_terminal) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Terminal address label</label>
                                <input type="text" name="fonepay_address" class="form-control rounded-3"
                                    value="{{ old('fonepay_address', $profile->fonepay_address) }}"
                                    placeholder="e.g. PARASI">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes (internal)</label>
                                <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes', $profile->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-card mb-3">
                    <div class="panel-heading">Logo</div>
                    <div class="panel-body p-4 text-center">
                        @php $logo = $profile->logo_path ?: 'images/logo.png'; @endphp
                        @if (is_file(public_path($logo)))
                            <img src="{{ asset($logo) }}" alt="Logo" class="img-fluid rounded-3 mb-3"
                                style="max-height:120px">
                        @endif
                        <input type="file" name="logo_file" accept="image/*" class="form-control rounded-3">
                        <div class="form-text">Shown on invoices and receipts.</div>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-heading">Payment QR</div>
                    <div class="panel-body p-4 text-center">
                        @php $qr = $profile->qr_path ?: 'images/qr_final.jpeg'; @endphp
                        @if (is_file(public_path($qr)))
                            <img src="{{ asset($qr) }}" alt="QR" class="img-fluid rounded-3 mb-3"
                                style="max-height:160px">
                        @endif
                        <input type="file" name="qr_file" accept="image/*" class="form-control rounded-3">
                        <div class="form-text">Used on invoice and receipt PDFs.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-primary text-white w-100 rounded-pill py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save institute profile
                </button>
            </div>
        </div>
    </form>
@endsection
