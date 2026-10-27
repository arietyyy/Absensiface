
@extends('layouts.app')

@section('title', 'Dashboard Absensi')

@section('content')
<h2 class="fw-bold mb-1">Dashboard</h2>
<p class="text-secondary mb-4">
    Ringkasan kehadiran karyawan hari ini.
</p>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card shadow-sm p-3">
            <div class="text-secondary">Total Karyawan Aktif</div>
            <div class="display-5 fw-bold">{{ $totalEmployees }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card shadow-sm p-3">
            <div class="text-secondary">Sudah Absen Masuk</div>
            <div class="display-5 fw-bold text-success">
                {{ $totalPresent }}
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card shadow-sm p-3">
            <div class="text-secondary">Belum Absen Masuk</div>
            <div class="display-5 fw-bold text-danger">
                {{ $totalNotPresent }}
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Absensi Hari Ini</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $attendance)
                        <tr>
                            <td>{{ $attendance->employee->employee_number }}</td>
                            <td>{{ $attendance->employee->name }}</td>
                            <td>{{ $attendance->check_in_at ?? '-' }}</td>
                            <td>{{ $attendance->check_out_at ?? '-' }}</td>
                            <td>
                                <span class="badge text-bg-success">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary">
                                Belum ada absensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
