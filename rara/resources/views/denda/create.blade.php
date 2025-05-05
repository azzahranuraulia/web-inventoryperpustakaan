<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Denda</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff8e1;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            text-align: center;
            margin: 20px 0;
        }
        h2 {
            color: #6d4c41;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .container {
            width: 80%;
            max-width: 500px;
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: #6d4c41;
        }
        input, select {
            padding: 12px;
            border: 1px solid #6d4c41;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }
        button {
            background-color: #6d4c41;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.5s;
            font-weight: bold;
        }
        button:hover {
            background-color: #5d4037;
        }
        .back-link {
            text-decoration: none;
            color: #6d4c41;
            font-weight: bold;
            display: block;
            text-align: center;
            margin-top: 15px;
        }
        .notification {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Tambah Denda</h2>
    </div>

    @if (session('success'))
        <div class="notification">
            {{ session('success') }}
        </div>
    @endif

    <div class="container">
        <form action="{{ route('denda.store') }}" method="POST">
            @csrf
            <label for="id_peminjaman">Pilih Peminjaman:</label>
            <select name="id_peminjaman" id="id_peminjaman" required>
                <option value="">-- Pilih Peminjaman --</option>
                @foreach ($peminjaman as $item)
                    <option value="{{ $item->id_peminjaman }}" 
                            data-tanggal-kembali="{{ $item->tanggal_kembali }}">
                        {{ $item->siswa->nama_siswa }} - {{ $item->barang->nama_barang }} ({{ $item->jumlah_pinjam }})
                    </option>
                @endforeach
            </select>
            <!-- Hapus input untuk jumlah_denda -->
            <button type="submit">Simpan</button>
        </form>
        <a href="{{ route('denda.index') }}" class="back-link">Kembali</a>
    </div>

    <script>
    document.getElementById('id_peminjaman').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const tanggalKembali = new Date(selectedOption.getAttribute('data-tanggal-kembali'));
        const tanggalDikembalikan = new Date(); // Tanggal hari ini

        // Konversi waktu agar selisih hari dihitung dengan benar
        tanggalKembali.setHours(0, 0, 0, 0);
        tanggalDikembalikan.setHours(0, 0, 0, 0);

        const diffTime = tanggalDikembalikan - tanggalKembali;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        const dendaPerHari = 2000;
        const totalDenda = diffDays > 0 ? diffDays * dendaPerHari : 0;

        // Anda bisa menyimpan totalDenda di session atau mengirimnya ke backend
        // Misalnya, Anda bisa menambahkan hidden input untuk mengirimkan total denda
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'jumlah_denda';
        hiddenInput.value = totalDenda;
        document.querySelector('form').appendChild(hiddenInput);
    });
    </script>
</body>
</html>