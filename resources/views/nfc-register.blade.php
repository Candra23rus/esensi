<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Kartu NFC</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<div class="container py-5">
    
    <div class="card shadow mx-auto" style="max-width:500px;">
        <div class="card-header bg-primary text-white text-center">
            <h5>Registrasi Kartu NFC</h5>
        </div>

        <div class="card-body text-center">

            <!-- STATUS -->
            <p id="status" class="text-muted">Klik tombol lalu tempel kartu</p>

            <!-- BUTTON SCAN -->
            <button id="btn-scan" class="btn btn-primary w-100 mb-3">
                Scan Kartu
            </button>

            <!-- FORM -->
            <form id="form-data" class="d-none">
                
                <input type="hidden" id="uid" name="uid">

                <div class="mb-3 text-start">
                    <label>Nama</label>
                    <input type="text" id="nama" class="form-control" required>
                </div>

                <div class="mb-3 text-start">
                    <label>NIS</label>
                    <input type="text" id="nis" class="form-control" required>
                </div>

                <div class="mb-3 text-start">
                    <label>Kelas</label>
                    <input type="text" id="kelas" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    Simpan Data
                </button>
            </form>

        </div>
    </div>

</div>

<script>
const btnScan = document.getElementById('btn-scan');
const statusText = document.getElementById('status');
const form = document.getElementById('form-data');
const uidInput = document.getElementById('uid');

btnScan.addEventListener('click', async () => {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();

            statusText.innerText = "Tempelkan kartu...";

            ndef.onreading = (event) => {
    const serialNumber = event.serialNumber;
    
    // Getar HP
    if (navigator.vibrate) navigator.vibrate(200);

    console.log("Kartu Terdeteksi, Serial:", serialNumber);

    // Tampilkan ke Input & UI
    uidInput.value = serialNumber;
    statusText.innerText = "ID Kartu: " + serialNumber;
    statusText.classList.replace('text-muted', 'text-success');

    // Munculkan Form
    form.classList.remove('d-none');
    
    // Sembunyikan tombol scan agar tidak double
    btnScan.classList.add('d-none');

    Swal.fire({
        icon: 'success',
        title: 'Kartu Terbaca!',
        text: 'ID: ' + serialNumber,
        timer: 1500
    });
};

        } catch (error) {
            Swal.fire('Error', 'Tidak bisa akses NFC', 'error');
        }
    } else {
        Swal.fire('Tidak Support', 'Gunakan Chrome Android', 'error');
    }
});


// SUBMIT FORM
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        uid: uidInput.value,
        nama: document.getElementById('nama').value,
        nis: document.getElementById('nis').value,
        kelas: document.getElementById('kelas').value
    };

    try {
        const response = await fetch('/api/nfc/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            Swal.fire('Sukses', result.message, 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }

    } catch (err) {
        Swal.fire('Error', 'Server error', 'error');
    }
});
</script>

</body>
</html>