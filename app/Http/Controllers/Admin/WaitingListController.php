<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaitingList;
use App\Models\rombong;
use App\Models\User;
use Illuminate\Http\Request;


class WaitingListController extends Controller
{
    public function waitinglist()
    {
        $users = User::where('status', 'pending')->get();
        $pendingRombong = WaitingList::with(['user.rombong', 'lapak.rombongs'])
            ->where('status', 'pending')
            ->get();

        return view('admin.waitinglist', compact('users', 'pendingRombong'));
    }

    public function approveAnggota($waitingListId)
    {
        $waitingList = WaitingList::with(['user', 'lapak'])->findOrFail($waitingListId);

        // Buat rombong ketika anggota disetujui
        rombong::create([
            'user_id'     => $waitingList->user_id,
            'lapak_id'    => $waitingList->lapak_id,
            'nama_jualan' => $waitingList->user->rombong->nama_jualan ?? '-',
            'jenis'       => 'tetap', 'sementara',
            'foto_rombong'=> $waitingList->user->rombong->foto_rombong ?? 'default.png',
        ]);

        $waitingList->update(['status' => 'disetujui']);

        return redirect()->route('admin.waitinglist')
            ->with('success', 'Anggota berhasil disetujui.');
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
