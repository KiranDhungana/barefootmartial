@if ($students->isNotEmpty())
    <form method="get" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <label class="small text-muted mb-0">{{ auth()->user()->isParent() ? 'View child:' : 'Student:' }}</label>
        <select name="student_id" class="form-select rounded-3" style="max-width:280px" onchange="this.form.submit()">
            @foreach ($students as $s)
                <option value="{{ $s->id }}" @selected($student && $student->id === $s->id)>
                    {{ $s->name }} ({{ $s->student_code }})
                </option>
            @endforeach
        </select>
    </form>
@endif
