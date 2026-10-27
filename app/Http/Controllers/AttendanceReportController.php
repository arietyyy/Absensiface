<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Attendance::with('employee')
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('attendance_date', $request->date);
            })
            ->when(
                !$request->filled('date') && $request->filled('month'),
                function ($query) use ($request) {
                    [$year, $month] = explode('-', $request->month);

                    $query->whereYear('attendance_date', $year)
                        ->whereMonth('attendance_date', $month);
                }
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_number', 'like', "%{$search}%");
                    });
                });
            });

        $attendances = $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => (clone $query)->count(),
            'checked_in' => (clone $query)
                ->whereNotNull('check_in_at')
                ->count(),
            'checked_out' => (clone $query)
                ->whereNotNull('check_out_at')
                ->count(),
        ];

        return view('reports.attendance', compact(
            'attendances',
            'summary'
        ));
    }
}
