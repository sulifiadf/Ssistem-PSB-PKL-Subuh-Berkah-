<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keuangan;


class KeuanganController extends Controller
{
    public function index()
    {
        $keuangans = keuangan::orderBy('tanggal', 'desc')->get();

        $totalPemasukan = keuangan::where('jenis', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = keuangan::where('jenis', 'pengeluaran')->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        return view('admin.keuangan.index', compact('keuangans', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        Keuangan::create($request->all());

        return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $keuangan = Keuangan::findOrFail($id);
        $keuangan->update($request->all());

        return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $keuangan = Keuangan::findOrFail($id);
        $keuangan->delete();

        return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan berhasil dihapus.');
    }

    public function edit($id)
    {
        $keuangan = Keuangan::findOrFail($id);
        return view('admin.keuangan.edit', compact('keuangan'));
    }
}
