{{-- QR Code Absensi Siswa --}}
{{-- Menampilkan QR code untuk check-in absensi --}}

<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">Scan QR Code Absensi</h4>
    </div>
    <div class="card-body">
        <p class="mb-3">Gunakan kamera atau scanner QR code untuk mendaftarkan kehadiran.</p>
        
        <div class="qr-code-container text-center">
            <svg class="qr-svg mb-3" width="200" height="200" viewBox="0 0 200 200">
                <path id="qr-path" fill="currentColor" d="M0 0h200v200H0z"/>
            </svg>
            <p class="mt-2 text-sm text-neutral-600">QR Code akan muncul di sini</p>
        </div>
        
        <div id="qr-loading" class="d-flex justify-content-center align-items py-4">
            <span class="spinner-border text-primary" role="status" aria-hidden="true"></span>
            <span>Sedang generate QR code...</span>
        </div>
        
        <button id="qr-generate-btn" class="btn btn-primary mt-3 w-100">
            <i class="fas fa-qr-code me-2"></i> Generate QR Code
        </button>
        
        <p class="mt-3 text-sm text-neutral-500">
            <small>QR code berisi: ID Siswa + Kelas + Tanggal Sesi Absensi</small>
        </p>
    </div>
</div>

<script>
document.getElementById('qr-generate-btn').addEventListener('click', function() {
    const studentId = {{ $student->id }};
    
    // Show loading state
    document.getElementById('qr-loading').classList.remove('d-none');
    document.getElementById('qr-generate-btn').disabled = true;
    
    // Fetch QR code from API
    fetch('/siswa/absensi/qr/' + studentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set QR code SVG
                const qrSvg = document.getElementById('qr-path');
                qrSvg.setAttribute('d', data.qr_code_svg);
                
                // Hide loading, show button
                document.getElementById('qr-loading').classList.add('d-none');
                document.getElementById('qr-generate-btn').disabled = false;
                
                // Show success message
                alert('QR code berhasil digenerate! Silakan scan.');
            } else {
                alert('Error: ' + data.message);
                document.getElementById('qr-loading').classList.add('d-none');
                document.getElementById('qr-generate-btn').disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal generate QR code');
            document.getElementById('qr-loading').classList.add('d-none');
            document.getElementById('qr-generate-btn').disabled = false;
        });
});

// Auto-generate QR code on page load
window.onload = function() {
    document.getElementById('qr-generate-btn').click();
};
</script>