<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Denda</title>
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
            margin-top: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input[type="text"], input[type="number"] {
            padding: 12px;
            border: 1px solid #6d4c41;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }
        button {
            background-color: #6d4c41;
            color: white;
            padding: 10px;
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
            margin-top: 20px;
            text-decoration: none;
            color: #6d4c41;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Edit Denda</h2>
    </div>

    @if (session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif

    <div class="container">
        <form action="{{ route('denda.update', $denda->id_denda) }}" method="POST">
            @csrf
            @method('PUT')
            <label for="id_peminjaman">Peminjaman:</label>
            <select name="id_peminjaman" id="id_peminjaman" required>
                @foreach ($peminjaman as $item)
                    <option value="{{ $item->id_peminjaman }}" {{ $item->id_peminjaman == $denda->id_peminjaman ? 'selected' : '' }}>{{ $item->siswa->nama }} - {{ $item->barang->nama_barang }}</option>
                @endforeach
            </select>
            <label for="jumlah_denda">Jumlah Denda:</label>
            <input type="number" name="jumlah_denda" value="{{ $denda->jumlah_denda }}" required>
            <label for="keterangan">Keterangan:</label>
            <input type="text" name="keterangan" value="{{ $denda->keterangan }}">
            <button type="submit">Update</button>
        </form>
        <a href="{{ route('denda.index') }}" class="back-link">Kembali</a>
    </div>
</body>
</html>