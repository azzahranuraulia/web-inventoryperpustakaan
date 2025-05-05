<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Siswa;
use App\Models\Denda;
use App\Models\Log; // Import model Log
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $peminjaman = Peminjaman::with('barang', 'siswa', 'denda')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where(function ($q) use ($query) {
                    $q->whereHas('siswa', function ($subQuery) use ($query) {
                        $subQuery->where('nama_siswa', 'LIKE', "%{$query}%");
                    })->orWhereHas('barang', function ($subQuery) use ($query) {
                        $subQuery->where('nama_barang', 'LIKE', "%{$query}%");
                    });
                });
            })->get();

        // Tambahkan status denda dan jumlah denda
        foreach ($peminjaman as $pinjam) {
            if ($pinjam->denda->isNotEmpty()) {
                $pinjam->status_denda = 'didenda';
                $pinjam->jumlah_denda = $pinjam->denda->sum('jumlah_denda');
            } else {
                $pinjam->status_denda = 'aman';
                $pinjam->jumlah_denda = 0;
            }
        }

        return view('peminjaman.index', compact('peminjaman', 'query'));
    }

    public function create()
    {
        $barang = Barang::all();
        $siswa = Siswa::all();
        return view('peminjaman.create', compact('barang', 'siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'nisn' => 'required|exists:siswa,nisn',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_pinjam',
        ]);
        
        // Cek stok barang
        $barang = Barang::find($request->id_barang);
        if ($barang->stok < $request->jumlah_pinjam) {
            return redirect()->back()->with('error', 'Stok buku tidak cukup untuk peminjaman ini.');
        }
        
        // Jika stok cukup, lanjutkan dengan peminjaman
        $data = $request->all();
        $data['status'] = 'dipinjam'; // Set status default saat peminjaman baru
        $peminjaman = Peminjaman::create($data);
        $barang->kurangiStok($request->jumlah_pinjam);
        
        // Cek apakah peminjaman melebihi waktu yang ditentukan
        $tanggal_kembali = Carbon::parse($request->tanggal_kembali);
        $tanggal_sekarang = Carbon::now();
        
        if ($tanggal_sekarang > $tanggal_kembali) {
            // Hitung denda
            $hari_terlambat = $tanggal_sekarang->diffInDays($tanggal_kembali);
            $denda = $hari_terlambat * 2000; // Denda per hari
            
            // Simpan denda
            Denda::create([
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'nisn' => $request->nisn, // Ambil dari request
                'jumlah_denda' => $denda,
                'keterangan' => "Terlambat {$hari_terlambat} hari, dikenakan denda " . number_format($denda, 2, ',', '.') . ".",
            ]);
            
            // Update status peminjaman menjadi 'didenda'
            $peminjaman->status = 'dipinjam';
            $peminjaman->save();
        }
        
        // Menyimpan log aktivitas
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'table_name' => 'peminjaman',
            'old_value' => null,
            'new_value' => json_encode($peminjaman),
            'ip_address' => request()->ip(),
        ]);
        
        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil ditambahkan');
    }

    public function edit($id_peminjaman)
    {
        $peminjaman = Peminjaman::findOrFail($id_peminjaman);
        $barang = Barang::all();
        $siswa = Siswa::all();
        return view('peminjaman.edit', compact('peminjaman', 'barang', 'siswa'));
    }

    public function update(Request $request, $id_peminjaman)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'nisn' => 'required|exists:siswa,nisn',
            'jumlah_pinjam' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_pinjam', // Pastikan ini sesuai dengan form
            'status' => 'required|string|in:Dipinjam,Dikembalikan,Didenda', // Validasi status
        ]);
    
        $peminjaman = Peminjaman::findOrFail($id_peminjaman);
        $oldValue = $peminjaman->toJson(); // Simpan nilai lama sebelum diubah
        $barang = Barang::find($peminjaman->id_barang);
    
        // Jika status diubah menjadi 'Dikembalikan', tambahkan stok
        if ($request->status == 'Dikembalikan') {
            $barang->tambahStok($peminjaman->jumlah_pinjam); // Kembalikan stok sebelumnya
            // Hapus baris ini
            // $peminjaman->tanggal_dikembalikan = Carbon::now(); // Set tanggal dikembalikan
        } else {
            // Jika status bukan 'Dikembalikan', kembalikan stok yang sudah ada
            $barang->tambahStok($peminjaman->jumlah_pinjam); // Kembalikan stok sebelumnya
            $barang->kurangiStok($request->jumlah_pinjam); // Kurangi stok baru
        }
    
        $peminjaman->update($request->all());
    
        // Menyimpan log aktivitas
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'table_name' => 'peminjaman',
            'old_value' => $oldValue,
            'new_value' => json_encode($peminjaman),
            'ip_address' => request()->ip(),
        ]);
    
        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil diperbarui');
    }

    public function destroy($id_peminjaman)
    {
        $peminjaman = Peminjaman::findOrFail($id_peminjaman);
        $oldValue = $peminjaman->toJson(); // Simpan nilai lama sebelum dihapus
        $barang = Barang::find($peminjaman->id_barang);
        $barang->tambahStok($peminjaman->jumlah_pinjam); // Kembalikan stok
        $peminjaman->delete();

        // Menyimpan log aktivitas
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'table_name' => 'peminjaman',
            'old_value' => $oldValue,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil dihapus');
    }

    public function kembalikan($id_peminjaman)
    {
        $peminjaman = Peminjaman::findOrFail($id_peminjaman);
        $tanggal_kembali = Carbon::now();
        $denda = 0;
        $keterangan = '';
    
        // Cek apakah tanggal_kembali lebih dari tanggal_kembali yang ditentukan
        if ($tanggal_kembali > Carbon::parse($peminjaman->tanggal_kembali)) { 
            // Hitung hari terlambat
            $hari_terlambat = $tanggal_kembali->diffInDays(Carbon::parse($peminjaman->tanggal_kembali));
            
            // Pastikan hari terlambat tidak negatif
            if ($hari_terlambat < 0) {
                $hari_terlambat = 0;
            }
    
            $denda = $hari_terlambat * 2000; // Denda per hari
            $keterangan = "Tidak mengembalikan buku selama {$hari_terlambat} hari dikenakan denda " . number_format($denda, 2, ',', '.') . ".";
            
            // Simpan denda jika ada
            Denda::create([
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'nisn' => $peminjaman->nisn,
                'jumlah_denda' => $denda,
                'keterangan' => $keterangan,
            ]);
            
            // Update status peminjaman menjadi 'didenda'
            $peminjaman->status = 'didenda';
        } else {
            // Jika tidak ada denda, status tetap 'dikembalikan'
            $peminjaman->status = 'dikembalikan';
        }
    
        // Update tanggal kembali
        $peminjaman->tanggal_kembali = $tanggal_kembali; // Menggunakan tanggal_kembali
        $peminjaman->save();
    
        // Tambah stok barang
        $barang = Barang::find($peminjaman->id_barang);
        $barang->tambahStok($peminjaman->jumlah_pinjam); // Tambah stok barang
    
        // Menyimpan log aktivitas
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'return',
            'table_name' => 'peminjaman',
            'old_value' => json_encode($peminjaman),
            'new_value' => json_encode($peminjaman), // Anda bisa menyesuaikan ini jika ingin mencatat perubahan
            'ip_address' => request()->ip(),
        ]);
    
        return redirect()->route('peminjaman.index')->with('success', 'Barang berhasil dikembalikan');
    }

    public function print(Request $request)
    {
        $query = $request->input('query'); // Mengambil input pencarian
        $peminjaman = Peminjaman::with('barang', 'siswa')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where(function ($q) use ($query) {
                    $q->whereHas('siswa', function ($subQuery) use ($query) {
                        $subQuery->where('nama_siswa', 'LIKE', "%{$query}%");
                    })->orWhereHas('barang', function ($subQuery) use ($query) {
                        $subQuery->where('nama_barang', 'LIKE', "%{$query}%");
                    });
                });
            })->get();
    
        return view('peminjaman.print', compact('peminjaman'));
    }
}