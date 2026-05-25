<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Student $student): View
    {
        BranchScope::assertStudentAccess($student);

        return view('erp.parents.create', compact('student'));
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $parent = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_PARENT,
        ]);

        $parent->children()->attach($student->id);

        return redirect()->route('erp.students.show', $student)
            ->with('success', 'Parent portal account created. They can log in at /login.');
    }
}
