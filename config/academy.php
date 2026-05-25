<?php

return [
    'membership_prefix' => env('ACADEMY_MEMBERSHIP_PREFIX', 'BFN'),

    'official_required_fields' => [
        'name',
        'branch_id',
        'join_date',
        'belt_rank',
    ],

    'student_statuses' => [
        'active',
        'pending_fee',
        'inactive',
        'scholarship',
        'suspended',
    ],

    'registration_statuses' => [
        'pending',
        'official',
    ],

    'erp_roles' => [
        'super_admin',
        'branch_admin',
        'accountant',
        'coach',
        'staff',
        'parent',
    ],

    'fee_types' => [
        'admission' => ['label' => 'Admission fee', 'default_price' => 0],
        'monthly' => ['label' => 'Monthly fee', 'default_price' => 0],
        'uniform' => ['label' => 'Uniform', 'default_price' => 0],
        'belt' => ['label' => 'Belt', 'default_price' => 0],
        'gloves' => ['label' => 'Gloves', 'default_price' => 0],
        'chest_guard' => ['label' => 'Chest guard', 'default_price' => 0],
        'shin_guard' => ['label' => 'Shin guard', 'default_price' => 0],
        'registration' => ['label' => 'Registration fee', 'default_price' => 0],
        'tournament' => ['label' => 'Tournament fee', 'default_price' => 0],
        'other' => ['label' => 'Other', 'default_price' => 0],
    ],

    'payment_methods' => ['cash', 'bank', 'card', 'other'],

    'scholarship_types' => [
        'none',
        'full_waiver',
        'partial',
        'sponsored',
    ],

    'invoice_statuses' => ['pending', 'partial', 'paid', 'overdue'],

    'default_late_fee' => (float) env('ACADEMY_DEFAULT_LATE_FEE', 0),

    'belt_ranks' => [
        'White',
        'Yellow',
        'Orange',
        'Green',
        'Blue',
        'Brown',
        'Red',
        'Black',
    ],

    'belt_months_between_exams' => (int) env('ACADEMY_BELT_MONTHS_BETWEEN', 3),

    'attendance_inactive_days' => (int) env('ACADEMY_ATTENDANCE_INACTIVE_DAYS', 14),

    'attendance_low_percent' => (int) env('ACADEMY_ATTENDANCE_LOW_PERCENT', 40),

    'expense_categories' => [
        'hall_rent',
        'equipment',
        'travel',
        'event',
        'utilities',
        'salary',
        'other',
    ],

    'logo_path' => env('ACADEMY_LOGO_PATH', 'images/logo.png'),

    'backup_path' => storage_path('app/backups'),

    'days_of_week' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
];

