<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class StudentImportService
{
    /**
     * @return array{created: int, errors: list<string>}
     */
    public function importFromCsv(UploadedFile $file, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return ['created' => 0, 'errors' => ['Could not read the uploaded file.']];
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return ['created' => 0, 'errors' => ['CSV file is empty.']];
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $created = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $result = $this->importRow($data, $user, $rowNum);
            if ($result === true) {
                $created++;
            } else {
                $errors[] = $result;
            }
        }

        fclose($handle);

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * @param  array<string, string>  $data
     * @return true|string error message
     */
    public function importRow(array $data, ?User $user, int $rowNum = 0): bool|string
    {
        $mapped = [
            'name' => $data['name'] ?? $data['full name'] ?? '',
            'belt_rank' => $data['belt'] ?? $data['belt_rank'] ?? '',
            'branch' => $data['branch'] ?? $data['branch_code'] ?? '',
            'join_date' => $data['joining_date'] ?? $data['join_date'] ?? '',
            'fee_status' => $data['fee_status'] ?? '',
            'uniform_status' => $data['uniform_status'] ?? '',
            'parent_name' => $data['parent_name'] ?? $data['parent name'] ?? '',
            'parent_contact' => $data['parent_contact'] ?? $data['parent contact'] ?? '',
            'phone' => $data['phone'] ?? $data['contact'] ?? '',
            'address' => $data['address'] ?? '',
            'status' => $data['status'] ?? 'active',
        ];

        $validator = Validator::make($mapped, [
            'name' => 'required|string|max:255',
            'belt_rank' => 'nullable|string|max:64',
            'join_date' => 'nullable|date',
            'phone' => 'nullable|string|max:50',
            'parent_contact' => 'nullable|string|max:50',
            'status' => 'nullable|in:'.implode(',', config('academy.student_statuses', [])),
        ]);

        if ($validator->fails()) {
            $suffix = $rowNum > 0 ? " (row {$rowNum})" : '';

            return implode(' ', $validator->errors()->all()).$suffix;
        }

        $branchId = $this->resolveBranchId($mapped['branch'], $user);
        if ($branchId === false) {
            return ($rowNum > 0 ? "Row {$rowNum}: " : '').'Branch not found: '.$mapped['branch'];
        }

        $student = new Student([
            'name' => $mapped['name'],
            'belt_rank' => $mapped['belt_rank'] ?: null,
            'branch_id' => $branchId,
            'join_date' => $mapped['join_date'] ?: null,
            'fee_status' => $mapped['fee_status'] ?: null,
            'uniform_status' => $mapped['uniform_status'] ?: null,
            'parent_name' => $mapped['parent_name'] ?: null,
            'parent_contact' => $mapped['parent_contact'] ?: null,
            'phone' => $mapped['phone'] ?: null,
            'address' => $mapped['address'] ?: null,
            'status' => $mapped['status'] ?: Student::STATUS_ACTIVE,
            'registration_status' => Student::REG_PENDING,
            'imported' => true,
        ]);

        if ($user?->branch_id && ! $user->isSuperAdmin()) {
            $student->branch_id = $user->branch_id;
        }

        $student->save();

        return true;
    }

    /**
     * @return int|false|null branch id, false if invalid when required
     */
    private function resolveBranchId(string $branchRef, ?User $user): int|false|null
    {
        if ($user?->branch_id && ! $user->isSuperAdmin()) {
            return (int) $user->branch_id;
        }

        if ($branchRef === '') {
            return Branch::query()->value('id');
        }

        $branch = Branch::query()
            ->where('code', $branchRef)
            ->orWhere('name', $branchRef)
            ->first();

        return $branch?->id ?? false;
    }

    public static function csvTemplateHeaders(): array
    {
        return [
            'name',
            'belt',
            'branch',
            'joining_date',
            'fee_status',
            'uniform_status',
            'parent_name',
            'parent_contact',
            'phone',
            'address',
            'status',
        ];
    }
}
