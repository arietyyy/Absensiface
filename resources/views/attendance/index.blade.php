@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">Absensi Face Recognition</h3>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <p class="text-secondary">
                Pilih jenis absensi, kemudian arahkan wajah ke kamera.
            </p>

            {{-- Pilihan jenis absensi --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" id="modeCheckIn"
                    class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Absen Masuk
                </button>

                <button type="button" id="modeCheckOut"
                    class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-right"></i>
                    Absen Pulang
                </button>
            </div>

            {{-- Kamera --}}
            <div class="mb-3">
                <video id="camera" autoplay muted playsinline
                    style="width:100%;max-width:640px;border-radius:12px;background:#222">
                </video>
            </div>

            {{-- Kontrol kamera --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" id="startCamera"
                    class="btn btn-primary">
                    <i class="bi bi-camera-video"></i>
                    Aktifkan Kamera
                </button>

                <button type="button" id="stopCamera"
                    class="btn btn-secondary">
                    <i class="bi bi-camera-video-off"></i>
                    Matikan Kamera
                </button>
            </div>

            {{-- Status --}}
            <div id="status" class="alert alert-info" role="status">
                Pilih jenis absensi, kemudian aktifkan kamera.
            </div>
        </div>
    </div>
</div>

{{-- Token CSRF --}}
<form id="csrfForm" class="d-none">
    @csrf
</form>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const video = document.getElementById('camera');
const statusBox = document.getElementById('status');

const startButton = document.getElementById('startCamera');
const stopButton = document.getElementById('stopCamera');

const modeInButton = document.getElementById('modeCheckIn');
const modeOutButton = document.getElementById('modeCheckOut');

const csrfInput = document.querySelector(
    '#csrfForm input[name="_token"]'
);

const csrfToken = csrfInput.value;

// Endpoint Laravel
const checkInUrl = @json(route('attendance.check-in'));
const checkOutUrl = @json(route('attendance.check-out'));

// Status aplikasi
let attendanceMode = 'check-in';
let cameraStream = null;
let detectionTimer = null;
let modelsLoaded = false;
let processing = false;
let attendanceRecorded = false;

// Mengatur tampilan pesan status
function setStatus(message, type = 'info') {
    statusBox.className = `alert alert-${type}`;
    statusBox.textContent = message;
}

// Menghentikan proses deteksi
function stopDetection() {
    if (detectionTimer) {
        clearInterval(detectionTimer);
        detectionTimer = null;
    }
}

// Memulai proses deteksi
function startDetection() {
    if (!cameraStream || detectionTimer || attendanceRecorded) {
        return;
    }

    detectionTimer = setInterval(() => {
        detectFace();
    }, 1500);
}

// Memilih mode absen masuk
modeInButton.addEventListener('click', () => {
    if (processing) {
        setStatus(
            'Tunggu proses absensi yang sedang berjalan.',
            'warning'
        );
        return;
    }

    attendanceMode = 'check-in';
    attendanceRecorded = false;

    modeInButton.className = 'btn btn-primary';
    modeOutButton.className = 'btn btn-outline-primary';

    setStatus('Mode absen masuk dipilih.', 'info');
    startDetection();
});

// Memilih mode absen pulang
modeOutButton.addEventListener('click', () => {
    if (processing) {
        setStatus(
            'Tunggu proses absensi yang sedang berjalan.',
            'warning'
        );
        return;
    }

    attendanceMode = 'check-out';
    attendanceRecorded = false;

    modeOutButton.className = 'btn btn-primary';
    modeInButton.className = 'btn btn-outline-primary';

    setStatus('Mode absen pulang dipilih.', 'info');
    startDetection();
});

// Memuat model AI
async function loadModels() {
    if (modelsLoaded) {
        return;
    }

    setStatus('Memuat model AI pengenalan wajah...', 'info');

    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]);

    modelsLoaded = true;
}

// Mengirim descriptor wajah ke Laravel
async function submitAttendance(descriptor) {
    const endpoint = attendanceMode === 'check-out'
        ? checkOutUrl
        : checkInUrl;

    const modeLabel = attendanceMode === 'check-out'
        ? 'absen pulang'
        : 'absen masuk';

    setStatus(
        `Wajah terdeteksi. Memproses ${modeLabel}...`,
        'info'
    );

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                descriptor: Array.from(descriptor)
            })
        });

        const data = await response.json();

        if (!response.ok || data.success !== true) {
            setStatus(
                data.message || 'Absensi gagal diproses.',
                'danger'
            );

            return false;
        }

        setStatus(
            data.message || 'Absensi berhasil dicatat.',
            'success'
        );

        return true;

    } catch (error) {
        console.error('Kesalahan pengiriman absensi:', error);

        setStatus(
            'Tidak dapat menghubungi server. Periksa koneksi Laravel.',
            'danger'
        );

        return false;
    }
}

// Mendeteksi dan memproses wajah
async function detectFace() {
    if (
        processing ||
        attendanceRecorded ||
        !cameraStream ||
        video.readyState < 2 ||
        video.videoWidth === 0
    ) {
        return;
    }

    processing = true;

    try {
        const results = await faceapi
            .detectAllFaces(
                video,
                new faceapi.TinyFaceDetectorOptions()
            )
            .withFaceLandmarks(true)
            .withFaceDescriptors();

        if (results.length === 0) {
            setStatus(
                'Wajah belum terdeteksi. Arahkan wajah ke kamera.',
                'info'
            );

            return;
        }

        if (results.length > 1) {
            setStatus(
                'Terdeteksi lebih dari satu wajah. Sisakan satu orang.',
                'warning'
            );

            return;
        }

        const success = await submitAttendance(
            results[0].descriptor
        );

        if (success) {
            attendanceRecorded = true;
            stopDetection();
        }

    } catch (error) {
        console.error('Kesalahan deteksi wajah:', error);

        setStatus(
            'Gagal mendeteksi wajah. Periksa model AI dan kamera.',
            'danger'
        );

    } finally {
        processing = false;
    }
}

// Mengaktifkan kamera
startButton.addEventListener('click', async () => {
    if (cameraStream) {
        return;
    }

    startButton.disabled = true;

    try {
        await loadModels();

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        });

        video.srcObject = cameraStream;

        await new Promise((resolve, reject) => {
            video.onloadedmetadata = resolve;
            video.onerror = reject;
        });

        await video.play();

        attendanceRecorded = false;

        setStatus(
            'Kamera aktif. Arahkan satu wajah ke kamera.',
            'info'
        );

        startDetection();

    } catch (error) {
        console.error('Kesalahan kamera:', error);

        setStatus(
            'Gagal mengaktifkan kamera atau memuat model AI: ' +
            error.message,
            'danger'
        );

        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        video.srcObject = null;

    } finally {
        startButton.disabled = false;
    }
});

// Mematikan kamera
stopButton.addEventListener('click', () => {
    stopDetection();

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    video.srcObject = null;
    attendanceRecorded = false;

    setStatus('Kamera dimatikan.', 'secondary');
});

// Membersihkan kamera ketika meninggalkan halaman
window.addEventListener('beforeunload', () => {
    stopDetection();

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endsection
