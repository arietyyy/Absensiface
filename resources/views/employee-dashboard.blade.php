
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Dashboard Karyawan</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5>Selamat datang, {{ $employee->name }}!</h5>
            <p class="mb-1">
                Nomor Karyawan: {{ $employee->employee_number }}
            </p>
            <p class="mb-1">
                Departemen: {{ $employee->department ?? '-' }}
            </p>
            <p class="mb-0">
                Jabatan: {{ $employee->position ?? '-' }}
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6>Status Absensi Hari Ini</h6>

                    @if(!$todayAttendance)
                        <span class="badge bg-secondary">
                            Belum Absen
                        </span>
                    @else
                        <span class="badge bg-success">
                            {{ ucfirst($todayAttendance->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6>Jam Masuk</h6>
                    <h4>
                        {{ $todayAttendance?->check_in_at ?? '--:--:--' }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6>Jam Pulang</h6>
                    <h4>
                        {{ $todayAttendance?->check_out_at ?? '--:--:--' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('attendance.index') }}"
           class="btn btn-primary">
            <i class="bi bi-camera"></i>
            Buka Absensi Wajah
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Riwayat Absensi Saya</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>
                                    {{ $attendance->attendance_date->format('d-m-Y') }}
                                </td>
                                <td>{{ $attendance->check_in_at ?? '-' }}</td>
                                <td>{{ $attendance->check_out_at ?? '-' }}</td>
                                <td>{{ ucfirst($attendance->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Belum ada riwayat absensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
