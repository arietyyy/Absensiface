
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Laporan Absensi Karyawan</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total Data Absensi</h6>
                    <h3>{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Sudah Absen Masuk</h6>
                    <h3>{{ $summary['checked_in'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Sudah Absen Pulang</h6>
                    <h3>{{ $summary['checked_out'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.attendance') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date"
                               name="date"
                               class="form-control"
                               value="{{ request('date') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <input type="month"
                               name="month"
                               class="form-control"
                               value="{{ request('month') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Nama / Nomor Karyawan</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari karyawan..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('reports.attendance') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Nomor Karyawan</th>
                            <th>Nama Karyawan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>
                                    {{ $attendances->firstItem() + $loop->index }}
                                </td>
                                <td>
                                    {{ $attendance->attendance_date->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $attendance->employee?->employee_number ?? '-' }}
                                </td>
                                <td>
                                    {{ $attendance->employee?->name ?? 'Data karyawan tidak tersedia' }}
                                </td>
                                <td>{{ $attendance->check_in_at ?? '-' }}</td>
                                <td>{{ $attendance->check_out_at ?? '-' }}</td>
                                <td>
                                    {{ ucfirst($attendance->status) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Tidak ada data absensi untuk filter tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
