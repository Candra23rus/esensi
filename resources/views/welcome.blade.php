<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi NFC</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<div class="d-flex justify-content-center align-items-center min-vh-100 p-3">
    
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 400px; width:100%;">
        
        <!-- HEADER -->
        <div class="bg-primary text-white text-center p-4 rounded-top-4">
            <div class="mb-3">
                <div class="bg-white bg-opacity-25 d-inline-flex p-3 rounded-circle">
                    <!-- ICON -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 11c0 3.5-1 6.8-2.7 9.5M8 11a4 4 0 118 0c0 1-.07 2-.2 3"/>
                    </svg>
                </div>
            </div>
            <h4 class="fw-bold">E-Absensi NFC</h4>
            <small>Silakan tempelkan kartu pada sensor HP</small>
        </div>

        <!-- BODY -->
        <div class="p-4 text-center">
            
            <!-- SCANNER -->
            <div id="scanner-visual" class="mb-4 d-none">
                <div class="position-relative d-inline-block">
                    <div class="spinner-grow text-primary position-absolute top-50 start-50 translate-middle"></div>
                    <div class="bg-light p-4 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" stroke="blue" fill="none" viewBox="0 0 24 24">
                            <path d="M7 19h10V5H7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- IDLE -->
            <div id="idle-visual" class="mb-4">
                <div class="bg-light p-4 rounded-circle d-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" stroke="gray" fill="none" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <!-- STATUS -->
            <div id="status-text" class="text-secondary fw-semibold mb-3">
                Scanner Belum Aktif
            </div>

            <!-- BUTTON -->
            <button id="btn-scan" class="btn btn-primary w-100 py-3 fw-bold">
                AKTIFKAN SCANNER
            </button>
        </div>

        <!-- FOOTER -->
        <div class="bg-light border-top p-3 text-center small text-muted d-flex justify-content-center gap-3">
            <div>
                <span class="badge bg-success">&nbsp;</span> System Ready
            </div>
            <div>
                <span class="badge bg-info">&nbsp;</span> PWA Enabled
            </div>
        </div>

    </div>
</div>

<p class="text-center text-muted small fst-italic">
    Pastikan NFC di pengaturan HP Anda sudah menyala
</p>

<script>
const btnScan = document.getElementById('btn-scan');
const statusText = document.getElementById('status-text');
const scannerVisual = document.getElementById('scanner-visual');
const idleVisual = document.getElementById('idle-visual');

btnScan.addEventListener('click', async () => {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            statusText.innerText = "Scanner Aktif. Dekatkan kartu...";

            // Gunakan onreading (lebih stabil di Chrome Android)
            ndef.onreading = (event) => {
    // 1. Ambil Serial Number
    const serialNumber = event.serialNumber;

    if (!serialNumber) {
        statusText.innerText = "Kartu terdeteksi, tapi Serial Number tidak terbaca.";
        return;
    }

    // 2. Feedback Getar
    if (navigator.vibrate) navigator.vibrate(200);

    // 3. Masukkan ke Input
    uidInput.value = serialNumber;
    statusText.innerText = "Terdeteksi: " + serialNumber;
    
    // 4. Munculkan Form & Sembunyikan Tombol
    form.classList.remove('d-none');
    btnScan.classList.add('d-none');

    console.log("UID Berhasil didapat:", serialNumber);
};

            // Tangkap jika sensor mendeteksi tapi gagal baca data
            ndef.onreadingerror = () => {
                statusText.innerText = "Kartu terdeteksi tapi gagal baca data. Coba posisi lain.";
                Swal.fire('Gagal Baca', 'Kartu tidak kompatibel atau posisi tidak pas.', 'warning');
            };

        } catch (error) {
            console.error(error);
            statusText.innerText = "Error: " + error;
        }
    }
});

async function sendAttendance(uid) {
    statusText.innerText = "Memproses Absensi...";

    try {
        const response = await fetch('/api/attendance/scan', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nfc_uid: uid })
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Absen!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error').then(() => location.reload());
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal ke server', 'error');
    }
}
</script>

</body>
</html>