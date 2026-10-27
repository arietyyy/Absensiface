<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('employees.index', [
            'employees' => Employee::latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_number' => [
                'required',
                'string',
                'max:50',
                'unique:employees,employee_number',
            ],
            'name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
        ]);

        Employee::create($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    // Menampilkan halaman pendaftaran wajah
    public function registerFace(Employee $employee): View
    {
        return view('employees.face', compact('employee'));
    }

    // Menyimpan descriptor wajah karyawan
    public function saveFace(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $validated = $request->validate([
            'face_descriptor' => ['required', 'array', 'size:128'],
            'face_descriptor.*' => [
                'required',
                'numeric',
                'between:-10,10',
            ],
        ]);

        $employee->update([
            'face_descriptor' => array_map(
                'floatval',
                $validated['face_descriptor']
            ),
            'face_registered_at' => now(),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Wajah karyawan berhasil didaftarkan.');
    }
}
