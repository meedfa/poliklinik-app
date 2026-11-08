<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(): View
    {
        $obats = Obat::orderBy('nama_obat')->get();

        return view('admin.obat.index', compact('obats'));
    }

    public function create(): View
    {
        return view('admin.obat.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kemasan' => ['nullable', 'string', 'max:35'],
            'harga' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        Obat::create($validated);

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data obat berhasil dibuat.');
    }

    public function edit(Obat $obat): View
    {
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat): RedirectResponse
    {
        $validated = $request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kemasan' => ['nullable', 'string', 'max:35'],
            'harga' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $obat->update($validated);

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat): RedirectResponse
    {
        if ($obat->detailPeriksas()->exists()) {
            return redirect()
                ->route('obat.index')
                ->with('error', 'Obat tidak dapat dihapus karena sudah digunakan dalam riwayat pemeriksaan.');
        }

        $obat->delete();

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data obat berhasil dihapus.');
    }
}
