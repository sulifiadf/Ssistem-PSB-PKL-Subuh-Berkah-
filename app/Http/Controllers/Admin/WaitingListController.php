<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaitingList;
use App\Models\rombong;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\NotifikasiPersetujuan;


class WaitingListController extends Controller
{
    public function waitinglist()
    {
        $users = User::where('status', 'pending')->get();
        $pendingRombong = WaitingList::with(['user.rombong', 'lapak.rombongs'])
            ->where('status', 'pending')
            ->whereNotNull('lapak_id')  // Hanya yang sudah memilih lapak
            ->get();

        return view('admin.waitinglist', compact('users', 'pendingRombong'));
    }

    public function store(Request $request, NotifikasiPersetujuan $wa)
    {
        $pengajuan = WaitingList::create([
            'user_id'  => auth()->id(),
            'lapak_id' => $request->lapak_id,
            'status'   => 'pending',
            'tanggal pengajuan' => now(),
        ]);

        $user  = $pengajuan->user;
        $lapak = $pengajuan->lapak;

        // Kirim notifikasi WA ke admin
        $message = "📢 Pengajuan Anggota Lapak\n\n"
            . "Nama User : {$user->name}\n"
            . "Email : {$user->email}\n"
            . "Lapak : {$lapak->nama_lapak}\n"
            . "Tanggal Ajukan : " . now()->format('d-m-Y H:i') . "\n\n"
            . "✅ Setujui / ❌ Tolak di dashboard admin.";

        $wa->notifyAdmin($message);

        return back()->with('status', 'Pengajuan berhasil dikirim, tunggu persetujuan admin.');
    } 

    public function approveAnggota($waitingListId)
    {
        $waitingList = WaitingList::with(['user', 'lapak'])->findOrFail($waitingListId);

        // CEK APAKAH USER SUDAH ADA DI LAPAK YANG SAMA
        $existingInSameLapak = rombong::where('user_id', $waitingList->user_id)
            ->where('lapak_id', $waitingList->lapak_id)
            ->first();
            
        if ($existingInSameLapak) {
            return redirect()->route('admin.waitinglist')
                ->with('error', 'User sudah ada di lapak tersebut!');
        }

        // Tentukan jenis anggota berdasarkan kondisi
        // Jika user sudah punya lapak lain, dia jadi anggota sementara
        // Jika user belum punya lapak, dia jadi anggota tetap
        $existingRombongInLapak = rombong::where('user_id', $waitingList->user_id)
            ->whereNotNull('lapak_id')
            ->first();
            
        $jenisAnggota = $existingRombongInLapak ? 'sementara' : 'tetap';

        // Ambil data rombong user (yang tanpa lapak atau lapak existing)
        $userRombong = rombong::where('user_id', $waitingList->user_id)->first();
        
        if (!$userRombong) {
            return redirect()->route('admin.waitinglist')
                ->with('error', 'User belum memiliki rombong!');
        }

        // Buat rombong baru khusus untuk lapak ini (untuk anggota sementara)
        // ATAU update rombong existing (untuk anggota tetap yang baru pertama kali)
        if ($jenisAnggota === 'sementara') {
            // User sudah punya lapak lain, buat rombong baru untuk lapak tambahan
            // Set berlaku hingga besok (1 hari)
            $berlakuHingga = now()->addDay()->toDateString();
            
            rombong::create([
                'user_id'       => $waitingList->user_id,
                'lapak_id'      => $waitingList->lapak_id,
                'nama_jualan'   => $userRombong->nama_jualan,
                'jenis'         => 'sementara',
                'foto_rombong'  => $userRombong->foto_rombong ?? 'default.png',
                'berlaku_hingga' => $berlakuHingga,
            ]);
        } else {
              // User baru pertama kali masuk lapak, update rombong existing
            $userRombong->update([
                'lapak_id' => $waitingList->lapak_id,
                'jenis'    => 'tetap',
                'berlaku_hingga' => null // Permanent member
            ]);
        }

        $waitingList->update(['status' => 'disetujui']);

        $statusMessage = $jenisAnggota === 'sementara' 
            ? "Anggota berhasil disetujui sebagai anggota SEMENTARA (berlaku hingga {$berlakuHingga}) karena sudah punya lapak lain."
            : 'Anggota berhasil disetujui sebagai anggota TETAP.';

        return redirect()->route('admin.waitinglist')
            ->with('success', $statusMessage);
    }

    public function rejectAnggota($waitingListId)
    {
        $waitingList = WaitingList::findOrFail($waitingListId);
        $waitingList->update(['status' => 'ditolak']);

        return redirect()->route('admin.waitinglist')
            ->with('success', 'Anggota ditolak.');
    }

    public function approveUser($userId) 
    { 
        $user = User::findOrFail($userId); 
        $user->update(['status' => 'approve']); 
        return redirect()->route('admin.waitinglist')->with('success', 'User berhasil disetujui'); 
    }

    public function rejectUser($userId) 
    { 
        $user = User::findOrFail($userId); 
        $user->update(['status' => 'rejected']); 
        return redirect()->route('admin.waitinglist')->with('success', 'User ditolak'); 
    }
    
}
