
@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<h2 class="fw-bold mb-1">Data Karyawan</h2>
<p class="text-secondary mb-4">
    Kelola data karyawan perusahaan.
</p>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="mb-3">Tambah Karyawan</h5>

        <form action="{{ route('employees.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NIP / ID Karyawan</label>
                    <input type="text" name="employee_number"
                           class="form-control"
                           value="{{ old('employee_number') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Departemen</label>
                    <input type="text" name="department"
                           class="form-control"
                           value="{{ old('department') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="position"
                           class="form-control"
                           value="{{ old('position') }}">
                </div>
            </div>

            <button class="btn btn-primary mt-3" type="submit">
                Simpan Karyawan
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Daftar Karyawan</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Status Wajah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_number }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department ?? '-' }}</td>
                            <td>{{ $employee->position ?? '-' }}</td>
                            <td>
                                @if($employee->face_descriptor)
                                    <span class="badge text-bg-success">
                                        Terdaftar
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Belum terdaftar
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('employees.face', $employee) }}"
                                class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-person-bounding-box"></i>
                                    {{ $employee->face_descriptor ? 'Daftar Ulang Wajah' : 'Daftarkan Wajah' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary">
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_number }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department ?? '-' }}</td>
                            <td>{{ $employee->position ?? '-' }}</td>
                            <td>
                                @if($employee->face_descriptor)
                                    <span class="badge text-bg-success">
                                        Terdaftar
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Belum terdaftar
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary">
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $employees->links() }}
    </div>
</div>
@endsection
