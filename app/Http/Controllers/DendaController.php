<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Log as LogModel; // Mengganti nama model Log
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Mengimpor Log facade
use Carbon\Carbon; // Pastikan Carbon di-import

class DendaController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $denda = Denda::with('peminjaman.siswa', 'peminjaman.barang')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->whereHas('peminjaman', function ($q) use ($query) {
                    $q->where('nisn', 'LIKE', "%{$query}%");
                });
            })->get();
    
        return view('denda.index', compact('denda', 'query'));
    }

    public function create()
    {
        // Ambil peminjaman yang berstatus 'didenda'
        $peminjaman = Peminjaman::where('status', 'didenda')->with('siswa', 'barang')->get();
        return view('denda.create', compact('peminjaman'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_peminjaman' => 'required|exists:peminjaman,id_peminjaman',
            'jumlah_denda' => 'required|numeric|min:0', // Pastikan ini ada
        ]);
        
        // Ambil peminjaman untuk menghitung denda
        $peminjaman = Peminjaman::findOrFail($request->id_peminjaman);
        
        // Hitung jumlah hari keterlambatan
        $tanggal_kembali = Carbon::parse($peminjaman->tanggal_kembali);
        $tanggal_dikembalikan = Carbon::now(); // Atau bisa menggunakan input dari form jika ada
        $hari_terlambat = $tanggal_dikembalikan->diffInDays($tanggal_kembali);
        
        // Debugging: Cek nilai hari terlambat
        Log::info("Hari terlambat: {$hari_terlambat}");
        
        // Jika terlambat, hitung denda
        if ($hari_terlambat > 0) {
            $jumlah_denda = $hari_terlambat * 2000; // Denda per hari
            
            // Simpan denda baru
            Denda::create([
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'nisn' => $peminjaman->siswa->nisn,
                'jumlah_denda' => $jumlah_denda,
                'keterangan' => "Tidak mengembalikan buku selama " . floor($hari_terlambat) . " hari dikenakan denda " . number_format($jumlah_denda, 2, ',', '.') . ".",
            ]);
            
            return redirect()->route('denda.index')->with('success', 'Denda berhasil ditambahkan');
        } else {
            // Jika tidak ada keterlambatan, Anda bisa mengarahkan kembali dengan pesan
            return redirect()->route('denda.index')->with('error', 'Tidak ada denda karena peminjaman tidak terlambat.');
        }
    }

    public function edit($id_denda)
    {
        $denda = Denda::findOrFail($id_denda);
        $peminjaman = Peminjaman::where('status', 'dikembalikan')->get();
        return view('denda.edit', compact('denda', 'peminjaman'));
    }

    public function update(Request $request, $id_denda)
    {
        $request->validate([
            'id_peminjaman' => 'required|exists:peminjaman,id_peminjaman',
            'jumlah_denda' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $denda = Denda::findOrFail($id_denda);
        $oldValue = $denda->toJson(); // Simpan nilai lama sebelum diubah
        $denda->update($request->all());

        // Menyimpan log aktivitas
        LogModel::create([ // Menggunakan LogModel untuk menyimpan log
            'user_id' => Auth::id(),
            'action' => 'update',
            'table_name' => 'denda',
            'old_value' => $oldValue,
            'new_value' => json_encode($denda),
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('denda.index')->with('success', 'Denda berhasil diperbarui');
    }

    public function destroy($id_denda)
    {
        $denda = Denda::findOrFail($id_denda);
        $oldValue = $denda->toJson(); // Simpan nilai lama sebelum dihapus

        // Menyimpan log aktivitas
        LogModel::create([ // Menggunakan LogModel untuk menyimpan log
            'user_id' => Auth::id(),
            'action' => 'delete',
            'table_name' => 'denda',
            'old_value' => $oldValue,
            'new_value' => null,
            'ip_address' => request()->ip(),
        ]);

        $denda->delete();
        return redirect()->route('denda.index')->with('success', 'Denda berhasil dihapus');
    }

    public function print(Request $request)
    {
        $query = $request->input('search');
        $denda = Denda::with('peminjaman.siswa', 'peminjaman.barang')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->whereHas('peminjaman', function ($q) use ($query) {
                    $q->where('nisn', 'LIKE', "%{$query}%");
                });
            })->get();

        return view('denda.print', compact('denda', 'query'));
    }
}