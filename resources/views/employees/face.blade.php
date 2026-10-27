
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-2">Pendaftaran Wajah Karyawan</h3>

    <p class="text-muted">
        Nomor Karyawan: {{ $employee->employee_number }}<br>
        Nama: {{ $employee->name }}<br>
        Departemen: {{ $employee->department ?? '-' }}
    </p>

    <div class="card shadow-sm">
        <div class="card-body">
            <video id="camera" autoplay muted playsinline
                style="width:100%;max-width:640px;background:#222;border-radius:12px">
            </video>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" id="startCamera"
                    class="btn btn-primary">
                    Aktifkan Kamera
                </button>

                <button type="button" id="captureFace"
                    class="btn btn-success" disabled>
                    Ambil Data Wajah
                </button>

                <button type="button" id="saveFace"
                    class="btn btn-warning" disabled>
                    Simpan Wajah
                </button>

                <button type="button" id="stopCamera"
                    class="btn btn-secondary">
                    Matikan Kamera
                </button>
            </div>

            <div id="status" class="alert alert-info mt-3">
                Aktifkan kamera untuk memulai pendaftaran wajah.
            </div>
        </div>
    </div>

    <form id="faceForm" method="POST"
        action="{{ route('employees.face.save', $employee) }}">
        @csrf
        <div id="descriptorInputs"></div>
    </form>

    <a href="{{ route('employees.index') }}"
        class="btn btn-outline-secondary mt-3">
        Kembali ke Data Karyawan
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const video = document.getElementById('camera');
const statusBox = document.getElementById('status');
const startButton = document.getElementById('startCamera');
const captureButton = document.getElementById('captureFace');
const saveButton = document.getElementById('saveFace');
const stopButton = document.getElementById('stopCamera');
const faceForm = document.getElementById('faceForm');
const descriptorInputs = document.getElementById('descriptorInputs');

let stream = null;
let descriptor = null;
let modelsLoaded = false;

async function loadModels() {
    if (modelsLoaded) return;

    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]);

    modelsLoaded = true;
}

startButton.addEventListener('click', async () => {
    startButton.disabled = true;

    try {
        statusBox.textContent = 'Memuat model AI...';
        await loadModels();

        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' },
            audio: false
        });

        video.srcObject = stream;

        await new Promise(resolve => {
            video.onloadedmetadata = resolve;
        });

        await video.play();

        statusBox.textContent =
            'Kamera aktif. Posisikan satu wajah dengan pencahayaan cukup.';
    } catch (error) {
        console.error(error);
        statusBox.textContent =
            'Gagal mengaktifkan kamera atau model: ' + error.message;
    } finally {
        startButton.disabled = false;
    }
});

captureButton.addEventListener('click', async () => {
    captureButton.disabled = true;

    try {
        if (!stream || video.readyState < 2) {
            throw new Error('Aktifkan kamera terlebih dahulu.');
        }

        const results = await faceapi
            .detectAllFaces(
                video,
                new faceapi.TinyFaceDetectorOptions()
            )
            .withFaceLandmarks(true)
            .withFaceDescriptors();

        if (results.length !== 1) {
            throw new Error(
                results.length === 0
                    ? 'Wajah belum terdeteksi.'
                    : 'Pastikan hanya satu wajah terlihat.'
            );
        }

        descriptor = Array.from(results[0].descriptor);

        descriptorInputs.replaceChildren();

        descriptor.forEach((value, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `face_descriptor[${index}]`;
            input.value = value;
            descriptorInputs.appendChild(input);
        });

        statusBox.textContent =
            'Descriptor wajah berhasil diambil. Klik Simpan Wajah.';
        saveButton.disabled = false;

    } catch (error) {
        console.error(error);
        statusBox.textContent = error.message;
    } finally {
        captureButton.disabled = false;
    }
});

saveButton.addEventListener('click', () => {
    if (!descriptor || descriptor.length !== 128) {
        statusBox.textContent = 'Ambil data wajah terlebih dahulu.';
        return;
    }

    faceForm.requestSubmit();
});

stopButton.addEventListener('click', () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }

    video.srcObject = null;
    captureButton.disabled = true;
    saveButton.disabled = true;
    statusBox.textContent = 'Kamera dimatikan.';
});

video.addEventListener('playing', () => {
    captureButton.disabled = false;
});

window.addEventListener('beforeunload', () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endsection
