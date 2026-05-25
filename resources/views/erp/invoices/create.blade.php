@extends('layouts.admin')

@section('title', 'Smart payment entry')
@section('page_title', 'Smart payment entry')
@section('page_subtitle', 'Fees, uniform & equipment')

@section('content')
    @if ($students->isEmpty())
        <div class="panel-card">
            <div class="panel-body p-4">
                <p class="text-muted mb-0">Add and officially register a student first.</p>
                <a href="{{ route('erp.students.create') }}" class="btn btn-outline-primary rounded-pill mt-3">Add student</a>
            </div>
        </div>
    @else
        <form method="post" action="{{ route('erp.invoices.store') }}" id="billing-form">
            @csrf
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="panel-card mb-3">
                        <div class="panel-heading">Student</div>
                        <div class="panel-body p-4">
                            <select name="student_id" id="student_id" class="form-select rounded-3" required>
                                @foreach ($students as $s)
                                    <option value="{{ $s->id }}" data-discount="{{ $s->discount_percent }}"
                                        data-scholarship="{{ $s->hasFullScholarship() ? '1' : '0' }}"
                                        @selected(old('student_id', $studentId) == $s->id)>
                                        {{ $s->student_code }} — {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="panel-card mb-3">
                        <div class="panel-heading">Fee items</div>
                        <div class="panel-body p-4">
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Item</th>
                                            <th style="width:90px">Qty</th>
                                            <th style="width:120px">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($feeTypes as $key => $fee)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input fee-check"
                                                        name="fees[{{ $key }}][selected]" value="1"
                                                        data-fee="{{ $key }}">
                                                </td>
                                                <td>{{ $fee['label'] }}</td>
                                                <td>
                                                    <input type="number" min="1" class="form-control form-control-sm fee-qty"
                                                        name="fees[{{ $key }}][quantity]" value="1" disabled>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control form-control-sm fee-price"
                                                        name="fees[{{ $key }}][unit_price]"
                                                        value="{{ $fee['default_price'] ?? 0 }}" disabled>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card mb-3">
                        <div class="panel-heading">Uniform & equipment (deducts stock)</div>
                        <div class="panel-body p-4" id="inventory-rows">
                            @forelse ($inventoryItems as $item)
                                <div class="row g-2 align-items-end mb-2 inv-row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input inv-check"
                                                id="inv-{{ $item->id }}">
                                            <label class="form-check-label" for="inv-{{ $item->id }}">
                                                {{ $item->name }} <span class="text-muted small">(stock:
                                                    {{ $item->stock_quantity }})</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select form-select-sm inv-size" disabled
                                            name="inventory[{{ $item->id }}][size]">
                                            <option value="">Size —</option>
                                            @foreach ($item->size_options ?? [] as $sz)
                                                <option value="{{ $sz }}">{{ $sz }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" min="1" class="form-control form-control-sm inv-qty" disabled
                                            name="inventory[{{ $item->id }}][quantity]" value="1">
                                        <input type="hidden" class="inv-id" disabled
                                            name="inventory[{{ $item->id }}][inventory_item_id]" value="{{ $item->id }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control form-control-sm inv-price" disabled
                                            name="inventory[{{ $item->id }}][unit_price]" value="{{ $item->unit_price }}">
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No inventory items. Run seeder or add stock.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="panel-card sticky-top" style="top:1rem">
                        <div class="panel-heading">Totals</div>
                        <div class="panel-body p-4">
                            <div class="mb-3">
                                <label class="form-check-label">
                                    <input type="checkbox" name="scholarship_waiver" value="1" class="form-check-input"
                                        id="scholarship_waiver">
                                    Scholarship / free student (waive all fees)
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Discount %</label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_percent"
                                    id="discount_percent" class="form-control rounded-3"
                                    value="{{ old('discount_percent', optional($student)->discount_percent ?? 0) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Late fee</label>
                                <input type="number" step="0.01" min="0" name="late_fee" id="late_fee"
                                    class="form-control rounded-3" value="{{ old('late_fee', 0) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Due date</label>
                                <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                            </div>
                            <hr>
                            <p class="mb-1 d-flex justify-content-between"><span>Subtotal</span><strong id="sum-subtotal">0.00</strong></p>
                            <p class="mb-1 d-flex justify-content-between text-muted"><span>Discount</span><span
                                    id="sum-discount">0.00</span></p>
                            <p class="mb-1 d-flex justify-content-between text-muted"><span>Late fee</span><span
                                    id="sum-late">0.00</span></p>
                            <p class="mb-3 d-flex justify-content-between fs-5"><span>Total</span><strong
                                    id="sum-total">0.00</strong></p>
                            <div class="mb-3">
                                <label class="form-label small">Payment now (optional)</label>
                                <input type="number" step="0.01" min="0" name="initial_payment" id="initial_payment"
                                    class="form-control rounded-3" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Payment method</label>
                                <select name="payment_method" class="form-select rounded-3">
                                    @foreach ($paymentMethods as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Notes</label>
                                <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes') }}</textarea>
                            </div>
                            <p class="small text-muted mb-3">Balance after payment: <strong id="sum-balance">0.00</strong></p>
                            <button type="submit" class="btn btn-admin-primary text-white w-100">Create invoice</button>
                            <a href="{{ route('erp.invoices.index') }}" class="btn btn-link w-100">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('billing-form');
            if (!form) return;

            function toggleFeeRow(row) {
                const on = row.querySelector('.fee-check').checked;
                row.querySelector('.fee-qty').disabled = !on;
                row.querySelector('.fee-price').disabled = !on;
            }

            function toggleInvRow(row) {
                const on = row.querySelector('.inv-check').checked;
                row.querySelectorAll('.inv-qty, .inv-price, .inv-size, .inv-id').forEach(el => el.disabled = !on);
            }

            document.querySelectorAll('tbody tr').forEach(tr => {
                const cb = tr.querySelector('.fee-check');
                if (cb) {
                    cb.addEventListener('change', () => toggleFeeRow(tr));
                }
            });
            document.querySelectorAll('.inv-row').forEach(row => {
                row.querySelector('.inv-check').addEventListener('change', () => toggleInvRow(row));
            });

            const studentSel = document.getElementById('student_id');
            const discountEl = document.getElementById('discount_percent');
            const scholarshipEl = document.getElementById('scholarship_waiver');

            studentSel?.addEventListener('change', () => {
                const opt = studentSel.selectedOptions[0];
                discountEl.value = opt.dataset.discount || 0;
                if (opt.dataset.scholarship === '1') scholarshipEl.checked = true;
                calc();
            });

            form.addEventListener('input', calc);
            form.addEventListener('change', calc);

            function calc() {
                let subtotal = 0;
                document.querySelectorAll('tbody tr').forEach(tr => {
                    const cb = tr.querySelector('.fee-check');
                    if (cb?.checked) {
                        const q = parseFloat(tr.querySelector('.fee-qty')?.value) || 1;
                        const p = parseFloat(tr.querySelector('.fee-price')?.value) || 0;
                        subtotal += q * p;
                    }
                });
                document.querySelectorAll('.inv-row').forEach(row => {
                    if (row.querySelector('.inv-check')?.checked) {
                        const q = parseFloat(row.querySelector('.inv-qty')?.value) || 1;
                        const p = parseFloat(row.querySelector('.inv-price')?.value) || 0;
                        subtotal += q * p;
                    }
                });

                const waiver = scholarshipEl.checked;
                const discPct = waiver ? 100 : (parseFloat(discountEl.value) || 0);
                const discount = Math.round(subtotal * discPct / 100 * 100) / 100;
                const late = parseFloat(document.getElementById('late_fee').value) || 0;
                const total = Math.max(0, Math.round((subtotal - discount + late) * 100) / 100);
                const paid = parseFloat(document.getElementById('initial_payment').value) || 0;
                const balance = Math.max(0, Math.round((total - paid) * 100) / 100);

                document.getElementById('sum-subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('sum-discount').textContent = discount.toFixed(2);
                document.getElementById('sum-late').textContent = late.toFixed(2);
                document.getElementById('sum-total').textContent = total.toFixed(2);
                document.getElementById('sum-balance').textContent = balance.toFixed(2);
            }
            calc();
        })();
    </script>
@endpush
