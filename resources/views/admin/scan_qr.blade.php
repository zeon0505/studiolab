@extends('layouts.admin')
@section('title', 'Scan QR Code Peminjaman')

@section('content')
<div class="max-w-lg mx-auto">

    {{-- Header --}}
    <div class="mb-6 text-center">
        <div class="w-14 h-14 bg-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-teal-600/30">
            <i class="fas fa-qrcode text-white text-xl"></i>
        </div>
        <h2 class="text-lg font-black text-slate-900">Verifikasi QR Code Peminjam</h2>
        <p class="text-xs text-slate-400 mt-1">Scan atau upload gambar QR Code dari bukti peminjaman</p>
    </div>

    {{-- Tab Switch --}}
    <div class="flex bg-slate-100 rounded-2xl p-1 mb-5">
        <button id="tab-camera" onclick="switchTab('camera')"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-900 shadow-sm">
            <i class="fas fa-camera mr-1.5"></i> Kamera
        </button>
        <button id="tab-upload" onclick="switchTab('upload')"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition-all text-slate-500">
            <i class="fas fa-image mr-1.5"></i> Upload Gambar QR
        </button>
    </div>

    {{-- === CAMERA TAB === --}}
    <div id="panel-camera">
        <div class="bg-slate-950 rounded-3xl overflow-hidden mb-4 relative" style="min-height: 300px;">

            {{-- Video element --}}
            <video id="qr-video" class="w-full block" style="min-height:300px; object-fit:cover;" playsinline></video>

            {{-- Canvas hidden for processing --}}
            <canvas id="qr-canvas" class="hidden"></canvas>

            {{-- Scan Frame Overlay --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-52 h-52">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-teal-400 rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-teal-400 rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-teal-400 rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-teal-400 rounded-br-lg"></div>
                    <div id="scan-line" class="absolute left-2 right-2 h-0.5 bg-teal-400 shadow-lg shadow-teal-400/50"></div>
                </div>
            </div>

            {{-- Status badge --}}
            <div id="cam-status" class="absolute bottom-3 left-0 right-0 flex justify-center">
                <span class="bg-black/60 text-white text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-2 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse inline-block"></span>
                    <span id="status-text">Memulai kamera...</span>
                </span>
            </div>
        </div>

        {{-- Camera Controls --}}
        <div class="flex gap-3 mb-4">
            <button id="btn-start" onclick="startCamera()"
                    class="flex-1 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center justify-center gap-2 transition-colors">
                <i class="fas fa-camera"></i> Mulai Kamera
            </button>
            <button id="btn-stop" onclick="stopCamera()"
                    class="hidden flex-1 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center gap-2 transition-colors">
                <i class="fas fa-stop"></i> Stop
            </button>
        </div>
    </div>

    {{-- === UPLOAD TAB === --}}
    <div id="panel-upload" class="hidden mb-4">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <label for="qr-file-input"
                   class="block border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center cursor-pointer hover:border-teal-400 hover:bg-teal-50/30 transition-all group">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
                    <i class="fas fa-upload text-slate-400 group-hover:text-teal-600 text-lg transition-colors"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">Klik untuk pilih gambar QR Code</p>
                <p class="text-xs text-slate-400 mt-1">PNG, JPG, JPEG — hasil screenshot atau unduhan QR</p>
                <input type="file" id="qr-file-input" accept="image/*" class="hidden">
            </label>

            {{-- Preview --}}
            <div id="upload-preview" class="hidden mt-4 text-center">
                <img id="preview-img" src="" alt="Preview QR" class="max-h-48 mx-auto rounded-xl border border-slate-100 shadow-sm">
                <p class="text-xs text-slate-500 mt-2 italic" id="upload-status">Memproses gambar...</p>
            </div>
        </div>
    </div>

    {{-- Result Panel --}}
    <div id="result-panel" class="hidden bg-emerald-50 border-2 border-emerald-200 rounded-3xl p-6 text-center mb-5">
        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
        </div>
        <p class="text-sm font-black text-emerald-900 mb-1">QR Berhasil Dipindai!</p>
        <p class="text-[11px] text-emerald-600 mb-4" id="result-url-text">Mengarahkan...</p>
        <a id="result-link" href="#"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-6 py-3 rounded-2xl transition-colors shadow-md">
            <i class="fas fa-arrow-right"></i> Lihat Detail Peminjaman
        </a>
        <p class="text-[10px] text-emerald-500 mt-3">Otomatis dialihkan dalam <span id="countdown">3</span> detik...</p>
    </div>

    {{-- Error Panel --}}
    <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-2xl p-4 text-center mb-5">
        <i class="fas fa-exclamation-circle text-red-400 text-2xl mb-2 block"></i>
        <p class="text-sm font-bold text-red-800">QR tidak dikenali</p>
        <p class="text-xs text-red-600 mt-1">QR Code ini bukan dari sistem peminjaman UPT atau tidak dapat dibaca.</p>
        <button onclick="resetAll()" class="mt-3 text-xs font-bold text-red-600 underline">Coba lagi</button>
    </div>

    {{-- Manual Input --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 mb-5">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">
            <i class="fas fa-keyboard mr-1.5"></i> Input Manual Nomor Booking
        </p>
        <p class="text-[10px] text-slate-400 mb-3">Ketik nomor ID (contoh: <strong>5</strong>) atau kode booking (contoh: <strong>BKG-0005</strong>)</p>
        <div class="flex gap-2">
            <input type="text" id="booking-id-input" placeholder="5 atau BKG-0005"
                   class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600"
                   onkeydown="if(event.key==='Enter') goToBooking()">
            <button onclick="goToBooking()"
                    class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-2 transition-colors">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>
        <div id="manual-error" class="hidden mt-2 text-xs text-red-500"><i class="fas fa-exclamation-circle mr-1"></i> Format tidak valid. Masukkan angka atau BKG-XXXX.</div>
    </div>

</div>

{{-- jsQR for QR decoding --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<style>
    #scan-line {
        animation: scanAnim 2s linear infinite;
    }
    @keyframes scanAnim {
        0%   { top: 6px; }
        50%  { top: calc(100% - 6px); }
        100% { top: 6px; }
    }
</style>

<script>
const BASE_URL = "{{ url('/') }}";
const ADMIN_BOOKINGS_URL = "{{ url('/admin/bookings') }}";
let videoStream = null;
let scanInterval = null;
let currentTab = 'camera';

// =====================
// TAB SWITCHING
// =====================
function switchTab(tab) {
    currentTab = tab;
    const tabCamera = document.getElementById('tab-camera');
    const tabUpload = document.getElementById('tab-upload');
    const panelCamera = document.getElementById('panel-camera');
    const panelUpload = document.getElementById('panel-upload');

    if (tab === 'camera') {
        tabCamera.className = 'flex-1 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-900 shadow-sm';
        tabUpload.className  = 'flex-1 py-2 rounded-xl text-xs font-bold transition-all text-slate-500';
        panelCamera.classList.remove('hidden');
        panelUpload.classList.add('hidden');
    } else {
        tabCamera.className = 'flex-1 py-2 rounded-xl text-xs font-bold transition-all text-slate-500';
        tabUpload.className  = 'flex-1 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-900 shadow-sm';
        panelCamera.classList.add('hidden');
        panelUpload.classList.remove('hidden');
        stopCamera();
    }
    resetPanels();
}

// =====================
// CAMERA SCANNER
// =====================
async function startCamera() {
    try {
        const constraints = { video: { facingMode: 'environment' }, audio: false };
        videoStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('qr-video');
        video.srcObject = videoStream;
        await video.play();

        document.getElementById('btn-start').classList.add('hidden');
        document.getElementById('btn-stop').classList.remove('hidden');
        document.getElementById('status-text').textContent = 'Memindai...';

        // Start scanning frames
        scanInterval = setInterval(scanVideoFrame, 300);
    } catch (err) {
        document.getElementById('status-text').textContent = 'Izin kamera ditolak';
        console.error('Camera error:', err);
    }
}

function stopCamera() {
    clearInterval(scanInterval);
    if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
        videoStream = null;
    }
    document.getElementById('btn-start').classList.remove('hidden');
    document.getElementById('btn-stop').classList.add('hidden');
    document.getElementById('status-text').textContent = 'Kamera berhenti';
}

function scanVideoFrame() {
    const video  = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    if (video.readyState !== video.HAVE_ENOUGH_DATA) return;

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });

    if (code) {
        stopCamera();
        handleQrResult(code.data);
    }
}

// =====================
// FILE UPLOAD SCANNER
// =====================
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('qr-file-input').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Show preview
                document.getElementById('upload-preview').classList.remove('hidden');
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('upload-status').textContent = 'Memproses gambar...';

                // Draw on canvas & scan
                const canvas = document.createElement('canvas');
                canvas.width  = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });

                if (code) {
                    document.getElementById('upload-status').textContent = 'QR ditemukan!';
                    handleQrResult(code.data);
                } else {
                    document.getElementById('upload-status').textContent = 'QR tidak terbaca di gambar ini.';
                    document.getElementById('error-panel').classList.remove('hidden');
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Auto start camera
    startCamera();
});

// =====================
// RESULT HANDLER
// =====================
function handleQrResult(text) {
    resetPanels();

    const isValid = text.startsWith(BASE_URL) && text.includes('/admin/bookings/');

    if (isValid) {
        document.getElementById('result-panel').classList.remove('hidden');
        document.getElementById('result-url-text').textContent = text;
        document.getElementById('result-link').href = text;

        // Countdown
        let count = 3;
        const cdEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            count--;
            cdEl.textContent = count;
            if (count <= 0) {
                clearInterval(timer);
                window.location.href = text;
            }
        }, 1000);
    } else {
        document.getElementById('error-panel').classList.remove('hidden');
    }
}

function resetPanels() {
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('error-panel').classList.add('hidden');
}

function resetAll() {
    resetPanels();
    document.getElementById('upload-preview').classList.add('hidden');
    document.getElementById('qr-file-input').value = '';
    if (currentTab === 'camera') startCamera();
}

// =====================
// MANUAL INPUT
// =====================
function goToBooking() {
    const raw = document.getElementById('booking-id-input').value.trim();
    const errEl = document.getElementById('manual-error');
    errEl.classList.add('hidden');

    if (!raw) return;

    let id = null;

    // Format: BKG-0005 atau BKG0005
    const bkgMatch = raw.match(/^BKG-?(\d+)$/i);
    if (bkgMatch) {
        id = parseInt(bkgMatch[1], 10);
    }
    // Format: angka murni
    else if (/^\d+$/.test(raw)) {
        id = parseInt(raw, 10);
    }

    if (id && id > 0) {
        window.location.href = ADMIN_BOOKINGS_URL + '/' + id;
    } else {
        errEl.classList.remove('hidden');
    }
}
</script>

@endsection
