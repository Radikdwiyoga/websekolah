{{-- QR Code Absensi Siswa (Permanen) --}}
{{-- Setiap siswa memiliki QR code permanen berisi NISN + Nama + Kelas --}}

<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">Scan QR Code Absensi Siswa</h4>
    </div>
    <div class="card-body">
        <p class="mb-3">Setiap siswa memiliki QR code permanen berisi NISN, nama, dan kelas. QR code ini tidak berubah per sesi absensi.</p>
        
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
        
        <div id="student-info" class="mt-4 p-3 bg-neutral-100 rounded text-center d-none">
            <h5 class="card-title mb-2">Identitas dari QR Code</h5>
            <p class="mb-1"><strong>NISN:</strong> <span id="qr-nis">--</span></p>
            <p class="mb-1"><strong>Nama:</strong> <span id="qr-nama">--</span></p>
            <p class="mb-1"><strong>Kelas:</strong> <span id="qr-kelas">--</span></p>
        </div>
        
        <p class="mt-3 text-sm text-neutral-500">
            <small>Scan QR code untuk check-in absensi. Data siswa akan terbaca otomatis.</small>
        </p>
    </div>
</div>

<script>
document.getElementById('qr-generate-btn').addEventListener('click', function() {
    // Ambil student ID dari context (akan di-set oleh Blade)
    const studentId = {{ $student->id }};
    
    // Show loading state
    document.getElementById('qr-loading').classList.remove('d-none');
    document.getElementById('qr-generate-btn').disabled = true;
    document.getElementById('student-info').addClass('d-none');
    
    // Fetch permanent QR code from API
    fetch('/siswa/absensi/qr/' + studentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set QR code SVG
                const qrSvg = document.getElementById('qr-path');
                qrSvg.setAttribute('d', data.qr_code_svg);
                
                // Tampilkan identitas siswa dari QR code
                document.getElementById('qr-nis').textContent = data.nis || '--';
                document.getElementById('qr-nama').textContent = data.student_name || '--';
                document.getElementById('qr-kelas').textContent = data.class_name || '--';
                document.getElementById('student-info').classList.remove('d-none');
                
                // Hide loading, show button
                document.getElementById('qr-loading').classList.add('d-none');
                document.getElementById('qr-generate-btn').disabled = false;
                
                // Simpan QR data untuk digunakan saat check-in
                window.lastQrData = data;
                
                // Show info message
                alert('QR code permanen berhasil digenerate!\nNISN: ' + (data.nis || '---') + '\nNama: ' + (data.student_name || '---') + '\nKelas: ' + (data.class_name || '---'));
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

// Fungsi untuk check-in via QR code scan
function checkInWithQr(qrData) {
    // Kirim data QR code ke server untuk check-in
    fetch('/siswa/absensi/qr-check-in', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            student_id: {{ $student->id }},
            class_id: {{ $student->class_id }},
            session_id: {{ isset($session->id) ? $session->id : 0 }},
            qr_data: JSON.stringify(qrData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Absensi berhasil: ' + data.message);
            // Refresh halaman atau tutup modal
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal melakukan check-in');
    });
}
</script>