<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\View\View;


class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        return view('dashboard', [
            'totalEmployees' => Employee::where('is_active', true)->count(),
            'totalPresent' => Attendance::where(
                'attendance_date', $today
            )->whereNotNull('check_in_at')->count(),
            'totalNotPresent' => Employee::where('is_active', true)->count()
                - Attendance::where('attendance_date', $today)
                    ->whereNotNull('check_in_at')->count(),
            'recentAttendances' => Attendance::with('employee')
                ->where('attendance_date', $today)
                ->latest('updated_at')
                ->limit(10)
                ->get(),
        ]);
    }
}