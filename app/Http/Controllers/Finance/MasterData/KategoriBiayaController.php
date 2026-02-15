<?php

namespace App\Http\Controllers\Finance\MasterData;

use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\KategoriBiaya;
use Illuminate\Http\Request;

class KategoriBiayaController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriBiaya::with('defaultCoa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('kode_kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $kategoriBiaya = $query->orderBy('nama_kategori')->paginate(config('app.pagination.master_data'));

        return view('dashboard.finance.masterdata.kategori_biaya.index', compact('kategoriBiaya'));
    }

    public function create()
    {
        $coas = COA::where('is_active', true)->orderBy('kode_coa')->get();

        return view('dashboard.finance.masterdata.kategori_biaya.create', compact('coas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategori_biaya,kode_kategori',
            'nama_kategori' => 'required|string|max:100',
            'default_coa_id' => 'nullable|exists:coa,coa_id',
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        KategoriBiaya::create($validated);

        return redirect()->route('finance.masterdata.kategori_biaya.index')
            ->with('success', 'Kategori biaya berhasil ditambahkan');
    }

    public function edit(KategoriBiaya $kategoriBiaya)
    {
        $coas = COA::where('is_active', true)->orderBy('kode_coa')->get();

        return view('dashboard.finance.masterdata.kategori_biaya.edit', compact('kategoriBiaya', 'coas'));
    }

    public function update(Request $request, KategoriBiaya $kategoriBiaya)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategori_biaya,kode_kategori,'.$kategoriBiaya->kategori_id.',kategori_id',
            'nama_kategori' => 'required|string|max:100',
            'default_coa_id' => 'nullable|exists:coa,coa_id',
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        $kategoriBiaya->update($validated);

        return redirect()->route('finance.masterdata.kategori_biaya.index')
            ->with('success', 'Kategori biaya berhasil diperbarui');
    }

    public function destroy(KategoriBiaya $kategoriBiaya)
    {
        if ($kategoriBiaya->pengajuan()->exists()) {
            return redirect()->route('finance.masterdata.kategori_biaya.index')
                ->with('error', "Kategori {$kategoriBiaya->nama_kategori} tidak dapat dihapus karena memiliki keterkaitan data transaksi.");
        }

        $kategoriBiaya->delete();

        return redirect()->route('finance.masterdata.kategori_biaya.index')
            ->with('success', 'Kategori biaya berhasil dihapus');
    }
}
