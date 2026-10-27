
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Absensi Karyawan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .sidebar { background: #172554; min-height: 100vh; }
        .sidebar a {
            display: block; color: #dbeafe; text-decoration: none;
            padding: 12px; border-radius: 8px; margin-bottom: 5px;
        }
        .sidebar a:hover { background: #1e40af; color: white; }
        .stat-card { border: 0; border-radius: 14px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <aside class="col-md-2 sidebar p-3">
        <h5 class="text-white mb-4">Absensi Face</h5>

        @auth
            @if(auth()->user()->role === 'admin')
                {{-- Menu Admin --}}
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('employees.index') }}">Data Karyawan</a>
                <a href="{{ route('attendance.index') }}">Absensi Wajah</a>
                 <a href="{{ route('reports.attendance') }}">Laporan Absensi</a>
            @elseif(auth()->user()->role === 'karyawan')
                {{-- Menu Karyawan --}}
                <a href="{{ route('employee.dashboard') }}">Dashboard Saya</a>
                <a href="{{ route('attendance.index') }}">Absensi Wajah</a>
            @endif

            <hr class="border-light">

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    Logout
                </button>
            </form>
        @endauth
    </aside>


        <main class="col-md-10 p-4">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
