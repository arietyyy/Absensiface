<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        abort_if(
            !$employee || !$employee->is_active,
            403,
            'Akun belum terhubung ke data karyawan aktif.'
        );

        $today = now()->toDateString();

        $todayAttendance = Attendance::where(
            'employee_id',
            $employee->id
        )
            ->whereDate('attendance_date', $today)
            ->first();

        $recentAttendances = Attendance::where(
            'employee_id',
            $employee->id
        )
            ->orderByDesc('attendance_date')
            ->limit(10)
            ->get();

        return view('employee-dashboard', compact(
            'employee',
            'todayAttendance',
            'recentAttendances'
        ));
    }
}
