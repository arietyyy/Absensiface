<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        return view('attendance.index');
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        $inputDescriptor = array_map(
            'floatval',
            $validated['descriptor']
        );

        $user = $request->user();

        // Karyawan hanya boleh mencocokkan wajah miliknya sendiri.
        if ($user->role === 'karyawan') {
            $employee = $user->employee;

            if (
                !$employee ||
                !$employee->is_active ||
                !is_array($employee->face_descriptor)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun belum terhubung ke data karyawan aktif atau wajah belum didaftarkan.',
                ], 422);
            }

            $employees = collect([$employee]);
        } elseif ($user->role === 'admin') {
            // Admin tetap dapat melakukan absensi untuk semua karyawan aktif.
            $employees = Employee::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses absensi.',
            ], 403);
        }

        $matchedEmployee = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($employees as $employee) {
            $savedDescriptor = $employee->face_descriptor;

            if (
                !is_array($savedDescriptor) ||
                count($savedDescriptor) !== 128
            ) {
                continue;
            }

            $sum = 0;

            foreach ($inputDescriptor as $i => $value) {
                $difference = $value - (float) $savedDescriptor[$i];
                $sum += $difference * $difference;
            }

            $distance = sqrt($sum);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $matchedEmployee = $employee;
            }
        }

        if (!$matchedEmployee || $bestDistance > 0.50) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak dikenali. Silakan periksa pendaftaran wajah Anda.',
            ], 422);
        }

        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $matchedEmployee->id,
                'attendance_date' => $today,
            ],
            [
                'check_in_at' => now()->format('H:i:s'),
                'status' => 'hadir',
            ]
        );

        if (!$attendance->wasRecentlyCreated) {
            return response()->json([
                'success' => true,
                'message' => 'Absensi hari ini sudah tercatat atas nama '
                    . $matchedEmployee->name . '.',
                'employee' => $matchedEmployee->name,
                'already_checked_in' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil! Selamat datang, '
                . $matchedEmployee->name . '.',
            'employee' => $matchedEmployee->name,
            'already_checked_in' => false,
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        $inputDescriptor = array_map(
            'floatval',
            $validated['descriptor']
        );

        $user = $request->user();

        // Karyawan hanya boleh mencocokkan wajah miliknya sendiri.
        if ($user->role === 'karyawan') {
            $employee = $user->employee;

            if (
                !$employee ||
                !$employee->is_active ||
                !is_array($employee->face_descriptor)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun belum terhubung ke data karyawan aktif atau wajah belum didaftarkan.',
                ], 422);
            }

            $employees = collect([$employee]);
        } elseif ($user->role === 'admin') {
            // Admin tetap dapat melakukan absensi untuk semua karyawan aktif.
            $employees = Employee::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses absensi.',
            ], 403);
        }

        $matchedEmployee = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($employees as $employee) {
            $savedDescriptor = $employee->face_descriptor;

            if (
                !is_array($savedDescriptor) ||
                count($savedDescriptor) !== 128
            ) {
                continue;
            }

            $sum = 0;

            foreach ($inputDescriptor as $i => $value) {
                $difference = $value - (float) $savedDescriptor[$i];
                $sum += $difference * $difference;
            }

            $distance = sqrt($sum);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $matchedEmployee = $employee;
            }
        }

        if (!$matchedEmployee || $bestDistance > 0.50) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak dikenali.',
            ], 422);
        }

        $attendance = Attendance::where(
            'employee_id',
            $matchedEmployee->id
        )
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->check_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini.',
            ], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json([
                'success' => true,
                'message' => 'Absen pulang sudah tercatat pada '
                    . $attendance->check_out_at . '.',
            ]);
        }

        $attendance->update([
            'check_out_at' => now()->format('H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil! Sampai jumpa, '
                . $matchedEmployee->name . '.',
        ]);
    }
}
