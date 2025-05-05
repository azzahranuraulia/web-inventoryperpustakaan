<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Denda</title>
    <style>
        /* Style yang sama seperti yang Anda berikan */
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
        .navbar {
            background-color: #ffe0b2;
            padding: 15px 0;
            display: flex;
            gap: 25px;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        .navbar a {
            color: #5d4037;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 20px;
            border-radius: 20px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .navbar a:hover {
            background-color: #6d4c41;
            color: white;
            border: 2px solid #ffe0b2;
        }
        .back-btn {
            position: absolute;
            left: 15px;
            bottom: -80px;
            background-color: #ffe0b2;
            color: white;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background-color: #5d4037;
        }
        .container {
            width: 80%;
            margin-top: 20px;
        }
        .btn {
            background-color: #6d4c41;
            color: white;
            padding: 20px 20px; /* Meningkatkan padding untuk ukuran tombol yang lebih besar */
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: bold;
            min-width: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn:hover {
            background-color: #5d4037;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center; /* Memastikan tombol sejajar secara vertikal */
            gap: 10px;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        form input[type="text"] {
            padding: 12px;
            border: 1px solid #6d4c41;
            border-radius: 8px;
            width: 250px;
            outline: none;
            font-size: 16px;
        }
        button {
            background-color: #6d4c41;
            color: white;
            padding: 8px 20px; /* Meningkatkan padding untuk ukuran tombol yang lebih besar */
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.5s;
            font-weight: bold;
            min-width: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        button:hover {
            background-color: #5d4037;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #6d4c41;
        }
        th {
            background-color: #ffe0b2;
            color: #5d4037;
        }
        tr {
            background-color: #fff3e0;
            transition: background-color 0.3s;
        }
        tr:hover {
            background-color: #ffccbc;
        }
        .notification {
            background-color: #ffe0b2;
            color: #6d4c41;
            padding: 10px;
            border: 1px solid #6d4c41;
            border-radius: 5px;
            margin-bottom: 20px;
            width: 80%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="{{ route('peminjaman.index') }}">📚 Peminjaman</a>
        <a href="{{ route('denda.index') }}">💰 Denda</a>
        <a href="{{ route('siswa.index') }}">👨‍🎓 Siswa</a>
        <a href="{{ route('barang.index') }}">📦 Barang</a>
        <a href="{{ route('home') }}" class="back-btn">🏠 Kembali</a>
    </div>
    <div class="header">
        <h2>Data Denda</h2>
    </div>
    <div class="container">
        @if (session('success'))
            <div class="notification">
                {{ session('success') }}
            </div>
        @endif
        <div class="search-container">
            <a href="{{ route('denda.create') }}" class="btn">➕ Tambah Denda</a>
            <form action="{{ route('denda.index') }}" method="GET">
                <input type="text" name="search" placeholder="Cari denda..." value="{{ request('search') }}">
                <button type="submit">🔍 Cari</button>
                <a href="{{ route('denda.print', ['query' => $query ?? '']) }}" class="btn">🖨️ Print</a>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID Denda</th>
                    <th>ID Peminjaman</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Jumlah Denda</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($denda as $item)
                    <tr>
                        <td>{{ $item->id_denda }}</td>
                        <td>{{ $item->id_peminjaman }}</td>
                        <td>{{ $item->nisn }}</td>
                        <td>{{ $item->peminjaman->siswa->nama_siswa }}</td> <!-- Mengambil nama siswa -->
                        <td>{{ number_format($item->jumlah_denda, 0, ',', '.') }}</td> <!-- Format jumlah denda tanpa desimal -->
                        <td>{{ $item->keterangan }}</td>
                        <td class="action-buttons">
                            <a href="{{ route('denda.edit', $item->id_denda) }}" class="btn">✏️ Edit</a>
                            <form action="{{ route('denda.destroy', $item->id_denda) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus?')" class="btn">🗑️ Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>