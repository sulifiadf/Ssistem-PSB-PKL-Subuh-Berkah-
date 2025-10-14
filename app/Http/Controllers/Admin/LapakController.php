<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lapak;
use App\Models\rombong;
use App\Models\User;


class LapakController extends Controller
{
    public function index()
    {
        // Ambil lapak beserta rombongs + user relasi
        $lapaks   = Lapak::with(['rombongs.user'])->get();
        $users    = User::all();
        $rombongs = rombong::with('user')->get();


        // kirim plural & singular supaya view yang lama tetap kompatibel
        return view('admin.lapak', [
            'lapaks'   => $lapaks,
            'rombongs' => $rombongs,
            'rombong'  => $rombongs,
            'users'    => $users,
            'user'     => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lapak'    => 'required|string|max:255',
            'rombong_ids'   => 'nullable|array',
            'rombong_ids.*' => 'integer',
            'rombong_id'    => 'nullable|integer', // opsi single id
        ]);

        // buat lapak
        $lapak = Lapak::create([
            'nama_lapak' => $validated['nama_lapak'],
        ]);

        // kumpulkan id rombong (boleh array atau single)
        $ids = $validated['rombong_ids'] ?? [];
        if (isset($validated['rombong_id'])) {
            $ids[] = (int) $validated['rombong_id'];
        }

        // bersihkan, unique
        $ids = array_values(array_filter(array_unique($ids)));

        if (!empty($ids)) {
            // ambil nama PK model Rombong (bisa 'id' atau 'rombong_id')
            $pk = (new Rombong)->getKeyName();

            // update kolom lapak_id pada rombong yang dipilih
            // pastikan tabel rombongs memang punya kolom lapak_id (nullable unsigned)
            rombong::whereIn($pk, $ids)
                ->update(['lapak_id' => $lapak->getKey()]);
        }

        return redirect()->route('admin.lapak.index')
                        ->with('success', 'Lapak berhasil ditambahkan.');
    }

    public function edit($userId)
    {
        $lapak = Lapak::with('rombongs.user')->findOrFail($userId);
        $users = User::all();
        $rombongs = rombong::with('user')->get();

        return view('admin.edit-lapak', compact('lapak', 'users', 'rombongs'));
    }

    public function update(Request $request, $userId)
    {
        $lapak = Lapak::findOrFail($userId);

        $validated = $request->validate([
            'nama_lapak'    => 'required|string|max:255',
            'rombong_ids'   => 'nullable|array',
            'rombong_ids.*' => 'integer',
            'rombong_id'    => 'nullable|integer', // opsi single id
        ]);

        // update nama lapak
        $lapak->update([
            'nama_lapak' => $validated['nama_lapak'],
        ]);

        // kumpulkan id rombong (boleh array atau single)
        $ids = $validated['rombong_ids'] ?? [];
        if (isset($validated['rombong_id'])) {
            $ids[] = (int) $validated['rombong_id'];
        }

        // bersihkan, unique
        $ids = array_values(array_filter(array_unique($ids)));

        // reset lapak_id semua rombong yang sebelumnya terhubung ke lapak ini
        rombong::where('lapak_id', $lapak->getKey())
            ->update(['lapak_id' => null]);

        if (!empty($ids)) {
            // ambil nama PK model Rombong (bisa 'id' atau 'rombong_id')
            $pk = (new Rombong)->getKeyName();

            // update kolom lapak_id pada rombong yang dipilih
            rombong::whereIn($pk, $ids)
                ->update(['lapak_id' => $lapak->getKey()]);
        }

        return redirect()->route('admin.lapak.index')
                        ->with('success', 'Lapak berhasil diperbarui.');
    }

    public function destroy($userId)
    {
        $lapak = Lapak::findOrFail($userId);
        
        // Reset lapak_id semua rombong yang terhubung ke lapak ini
        rombong::where('lapak_id', $lapak->getKey())
            ->update(['lapak_id' => null]);
            
        $lapak->delete();
        return redirect()->route('admin.lapak.index')->with('success', 'Lapak berhasil dihapus');
    }
    
    public function addAnggota(Request $request)
    {
        $validated = $request->validate([
            'lapak_id'      => 'required|integer|exists:lapaks,lapak_id',
            'rombong_ids'   => 'required|array|min:1',
            'rombong_ids.*' => 'integer|exists:rombongs,rombong_id',
        ]);

        $lapak = Lapak::findOrFail($validated['lapak_id']);
        $pk = (new Rombong)->getKeyName();

        // Update lapak_id pada rombong yang dipilih
        $updated = rombong::whereIn($pk, $validated['rombong_ids'])
            ->update(['lapak_id' => $lapak->getKey()]);

        $message = $updated > 0 
            ? "Berhasil menambahkan {$updated} anggota ke lapak {$lapak->nama_lapak}"
            : "Tidak ada anggota yang ditambahkan";

        return redirect()->route('admin.lapak.index')->with('success', $message);
    }
    
    public function removeAnggota(Request $request)
    {
        $validated = $request->validate([
            'lapak_id'   => 'required|integer|exists:lapaks,lapak_id',
            'rombong_id' => 'required|integer|exists:rombongs,rombong_id',
        ]);

        $lapak = Lapak::findOrFail($validated['lapak_id']);
        $pk = (new Rombong)->getKeyName();

        // Reset lapak_id pada rombong yang dipilih
        $updated = rombong::where($pk, $validated['rombong_id'])
            ->where('lapak_id', $lapak->getKey())
            ->update(['lapak_id' => null]);

        $message = $updated > 0 
            ? "Anggota berhasil dihapus dari lapak {$lapak->nama_lapak}"
            : "Anggota tidak ditemukan atau sudah tidak ada di lapak";

        return redirect()->route('admin.lapak.index')->with('success', $message);
    }
}
