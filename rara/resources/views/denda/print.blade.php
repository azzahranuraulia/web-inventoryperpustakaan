<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Denda</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff8e1;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #6d4c41;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #6d4c41;
        }
        th {
            background-color: #ffe0b2;
        }
    </style>
</head>
<body>
    <h2>Daftar Denda</h2>
    <table>
        <thead>
            <tr>
                <th>ID Denda</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Jumlah Denda</th>
                <th>Keterangan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($denda as $item)
                <tr>
                    <td>{{ $item->id_denda }}</td>
                    <td>{{ $item->nisn }}</td>
                    <td>{{ $item->nama_siswa }}</td>
                    <td>{{ number_format($item->jumlah_denda, 2, ',', '.') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>{{ $item->created_at->format('d-m-Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>