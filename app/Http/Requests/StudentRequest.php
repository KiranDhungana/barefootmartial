<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canManageStudents();
    }

    public function rules(): array
    {
        $statuses = config('academy.student_statuses', []);

        return [
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:8',
            'parent_name' => 'nullable|string|max:255',
            'parent_contact' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:50',
            'coach_name' => 'nullable|string|max:255',
            'belt_rank' => 'nullable|string|max:64',
            'batch_timing' => 'nullable|string|max:128',
            'status' => ['nullable', Rule::in($statuses)],
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'scholarship_type' => ['nullable', Rule::in(config('academy.scholarship_types', []))],
            'scholarship_notes' => 'nullable|string|max:500',
            'fee_status' => 'nullable|string|max:64',
            'uniform_status' => 'nullable|string|max:64',
            'join_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && $user->isBranchScoped()) {
            $this->merge(['branch_id' => $user->branch_id]);
        }
    }
}
