@php($cardClass = $cardClass ?? 'panel-card')
@if ($eventCertificates->isNotEmpty() || $studentCertificates->isNotEmpty())
    <div class="col-12">
        <div class="{{ $cardClass }}">
            <div class="{{ ($cardClass === 'panel-card') ? 'panel-heading' : 'p-4 pb-0' }}">
                @if ($cardClass !== 'panel-card')
                    <h2 class="h6 fw-bold text-uppercase text-muted mb-0">Certificates</h2>
                @else
                    Certificates
                @endif
            </div>
            <div class="{{ ($cardClass === 'panel-card') ? 'panel-body p-4' : 'p-4 pt-3' }}">
                @if ($eventCertificates->isNotEmpty())
                    <h6 class="text-muted text-uppercase small mb-3">Event certificates</h6>
                    <div class="row g-3 mb-4">
                        @foreach ($eventCertificates as $reg)
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="d-flex gap-3 align-items-start">
                                        @if ($reg->certificateIsImage())
                                            <a href="{{ $reg->certificate_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $reg->certificate_url }}" alt="Certificate"
                                                    class="rounded-3" style="width:72px;height:72px;object-fit:cover">
                                            </a>
                                        @else
                                            <div class="text-center" style="width:72px">
                                                <i class="fa-solid fa-file-pdf fa-2x text-danger"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $reg->certificate_title ?: $reg->event?->title }}</div>
                                            <div class="small text-muted">{{ $reg->event?->title }}</div>
                                            @if ($reg->certificate_number)
                                                <div class="small">No: {{ $reg->certificate_number }}</div>
                                            @endif
                                            @if ($reg->certificate_issued_on)
                                                <div class="small text-muted">Issued {{ $reg->certificate_issued_on->format('M j, Y') }}</div>
                                            @endif
                                            <a href="{{ $reg->certificate_url }}" target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-primary rounded-pill mt-2">
                                                View certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($studentCertificates->isNotEmpty())
                    <h6 class="text-muted text-uppercase small mb-3">Other certificates</h6>
                    <div class="row g-3">
                        @foreach ($studentCertificates as $cert)
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="d-flex gap-3 align-items-start">
                                        @if ($cert->isImage())
                                            <a href="{{ $cert->file_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $cert->file_url }}" alt="{{ $cert->title }}"
                                                    class="rounded-3" style="width:72px;height:72px;object-fit:cover">
                                            </a>
                                        @else
                                            <div class="text-center" style="width:72px">
                                                <i class="fa-solid fa-file-pdf fa-2x text-danger"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $cert->title }}</div>
                                            @if ($cert->issued_on)
                                                <div class="small text-muted">Issued {{ $cert->issued_on->format('M j, Y') }}</div>
                                            @endif
                                            <a href="{{ $cert->file_url }}" target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-primary rounded-pill mt-2">
                                                View certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
