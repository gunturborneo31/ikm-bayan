<?php

// app/Http/Controllers/SubJenisController.php
// app/Http/Controllers/SubJenisController.php
namespace App\Http\Controllers;

use App\Models\BagianLayanan;
use App\Models\SubJenis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\BagianOptions;

class SubJenisController extends Controller
{
    private function defaultBagianList(): array
    {
        return BagianOptions::idNameMap();
    }

    private function getBagianList(): array
    {
        return BagianOptions::idNameMap();
    }

   public function index(Request $request)
    {
        $_GET['Tahun'] = \App\Support\SurveyFilter::year();
        $_GET['bagian'] = \App\Support\SurveyFilter::pilarCsv();

        $bagianList = $this->getBagianList();

        // Ambil keterangan dari GET
        $ket = strtolower($request->get('keterangan'));

        $codeToId = [];
        if (Schema::hasTable('bagian_layanan')) {
            $codeToId = BagianLayanan::where('is_active', true)->pluck('id', 'kode')->toArray();
        }

        // --- LOGIKA FILTER ---

        // 1️⃣ Jika keterangan kosong → tampil semua
        if (!$ket) {
            $filtered = $bagianList;
        }
        // 2️⃣ Jika admin atau ortal → tampil semua
        elseif (in_array($ket, ['admin', 'ortal'], true)) {
            $filtered = $bagianList;
        }
        // 3️⃣ Jika keterangan cocok ke kode bagian aktif
        elseif (isset($codeToId[$ket]) && isset($bagianList[$codeToId[$ket]])) {
            $bagianId = $codeToId[$ket];
            $filtered = [$bagianId => $bagianList[$bagianId]];
        }
        // 4️⃣ Jika tidak ditemukan → hasil kosong
        else {
            $filtered = [];
        }

        return view('sub_jenis.index', [
            'bagianList' => $filtered,
        ]);
    }



    // LIST: kembalikan data terkelompok per bidang
    public function list(Request $req)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'bidangOptions' => [],
                'message' => 'Tabel sub_jenis belum tersedia.',
            ]);
        }

        $req->validate([
            'bagian' => ['required', 'integer', 'min:1', 'max:255'],
            'q'      => ['nullable','string','max:100'],
        ]);

        if (Schema::hasTable('bagian_layanan')
            && !BagianLayanan::where('id', (int) $req->bagian)->where('is_active', true)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bagian tidak ditemukan'], 422);
        }

        $q = trim((string)$req->q);
        $base = SubJenis::where('bagian', $req->bagian)
            ->where('bidang', 'PROGRAM'); // hanya program
        if ($q !== '') {
            $base->where(function ($s) use ($q) {
                $s->where('jenis','like',"%{$q}%");
            });
        }
        $rows = $base->orderBy('jenis')->get();

        // grupkan per bidang
        $grouped = $rows->groupBy('bidang')->map(function($items, $bidang){
            return [
                'bidang' => $bidang,
                'items'  => $items->values(), // tiap item punya id, bagian, bidang, jenis
            ];
        })->values();

        // kirim juga daftar bidang yang sudah ada (untuk datalist di form)
        $bidangOptions = SubJenis::where('bagian',$req->bagian)
                           ->select('bidang')->distinct()->orderBy('bidang')->pluck('bidang');

        return response()->json([
            'ok' => true,
            'data' => $grouped,
            'bidangOptions' => $bidangOptions,
        ]);
    }

    public function store(Request $req)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_jenis belum tersedia.'], 500);
        }

        $data = $req->validate([
            'bagian' => ['required', 'integer', 'min:1', 'max:255'],
            'bidang' => ['required','string','max:255'],
            'jenis'  => ['required','string','max:255'],
        ]);

        if (Schema::hasTable('bagian_layanan')
            && !BagianLayanan::where('id', (int) $data['bagian'])->where('is_active', true)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bagian tidak ditemukan'], 422);
        }

        // opsional: cegah duplikat persis
        $exists = SubJenis::where($data)->exists();
        if ($exists) {
            return response()->json(['ok'=>false,'message'=>'Data sudah ada'], 422);
        }

        $item = SubJenis::create($data);
        return response()->json(['ok'=>true, 'data'=>$item]);
    }

    public function update(Request $req, $id)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_jenis belum tersedia.'], 500);
        }

        $item = SubJenis::findOrFail($id);
        $data = $req->validate([
            'bagian' => ['required', 'integer', 'min:1', 'max:255'],
            'bidang' => ['required','string','max:255'],
            'jenis'  => ['required','string','max:255'],
        ]);

        if (Schema::hasTable('bagian_layanan')
            && !BagianLayanan::where('id', (int) $data['bagian'])->where('is_active', true)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bagian tidak ditemukan'], 422);
        }

        $dupe = SubJenis::where($data)->where('id','<>',$id)->exists();
        if ($dupe) {
            return response()->json(['ok'=>false,'message'=>'Duplikat data'], 422);
        }

        $item->update($data);
        return response()->json(['ok'=>true,'data'=>$item]);
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_jenis belum tersedia.'], 500);
        }

        SubJenis::findOrFail($id)->delete();
        return response()->json(['ok'=>true]);
    }

    public function updateJenis(Request $req)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_jenis belum tersedia.'], 500);
        }

        $data = $req->validate([
            'bagian' => ['required', 'integer', 'min:1', 'max:255'],
            'old_bidang' => ['required', 'string', 'max:255'],
            'new_bidang' => ['required', 'string', 'max:255'],
        ]);

        if (Schema::hasTable('bagian_layanan')
            && !BagianLayanan::where('id', (int) $data['bagian'])->where('is_active', true)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bagian tidak ditemukan'], 422);
        }

        $oldBidang = trim($data['old_bidang']);
        $newBidang = trim($data['new_bidang']);

        if ($oldBidang === '' || $newBidang === '') {
            return response()->json(['ok' => false, 'message' => 'Jenis wajib diisi'], 422);
        }

        $updated = SubJenis::where('bagian', $data['bagian'])
            ->where('bidang', $oldBidang)
            ->update(['bidang' => $newBidang]);

        if ($updated < 1) {
            return response()->json(['ok' => false, 'message' => 'Jenis tidak ditemukan'], 404);
        }

        return response()->json(['ok' => true, 'updated' => $updated]);
    }

    public function destroyJenis(Request $req)
    {
        if (!Schema::hasTable('sub_jenis')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_jenis belum tersedia.'], 500);
        }

        $data = $req->validate([
            'bagian' => ['required', 'integer', 'min:1', 'max:255'],
            'bidang' => ['required', 'string', 'max:255'],
        ]);

        if (Schema::hasTable('bagian_layanan')
            && !BagianLayanan::where('id', (int) $data['bagian'])->where('is_active', true)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bagian tidak ditemukan'], 422);
        }

        $deleted = SubJenis::where('bagian', $data['bagian'])
            ->where('bidang', trim($data['bidang']))
            ->delete();

        if ($deleted < 1) {
            return response()->json(['ok' => false, 'message' => 'Jenis tidak ditemukan'], 404);
        }

        return response()->json(['ok' => true, 'deleted' => $deleted]);
    }

    public function bagianList()
    {
        if (!Schema::hasTable('bagian_layanan')) {
            return response()->json([
                'ok' => true,
                'data' => collect($this->defaultBagianList())->map(function ($nama, $id) {
                    return ['id' => (int) $id, 'nama' => $nama, 'kode' => Str::slug($nama), 'is_active' => true];
                })->values(),
            ]);
        }

        $data = BagianLayanan::where('is_active', true)->orderBy('id')->get(['id', 'nama', 'kode', 'is_active']);
        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function bagianStore(Request $req)
    {
        if (!Schema::hasTable('bagian_layanan')) {
            return response()->json(['ok' => false, 'message' => 'Tabel bagian_layanan belum tersedia.'], 500);
        }

        $data = $req->validate([
            'nama' => ['required', 'string', 'max:150'],
        ]);

        $nama = trim($data['nama']);
        if ($nama === '') {
            return response()->json(['ok' => false, 'message' => 'Nama bagian wajib diisi'], 422);
        }

        $kode = Str::slug($nama);
        if ($kode === '') {
            $kode = 'bagian-' . time();
        }

        $baseKode = $kode;
        $suffix = 2;
        while (BagianLayanan::where('kode', $kode)->exists()) {
            $kode = $baseKode . '-' . $suffix;
            $suffix++;
        }

        $item = BagianLayanan::create([
            'nama' => $nama,
            'kode' => $kode,
            'is_active' => true,
        ]);

        return response()->json(['ok' => true, 'data' => $item]);
    }

    public function bagianUpdate(Request $req, $id)
    {
        if (!Schema::hasTable('bagian_layanan')) {
            return response()->json(['ok' => false, 'message' => 'Tabel bagian_layanan belum tersedia.'], 500);
        }

        $item = BagianLayanan::where('is_active', true)->findOrFail($id);
        $data = $req->validate([
            'nama' => ['required', 'string', 'max:150'],
        ]);

        $nama = trim($data['nama']);
        if ($nama === '') {
            return response()->json(['ok' => false, 'message' => 'Nama bagian wajib diisi'], 422);
        }

        $kode = Str::slug($nama);
        if ($kode === '') {
            $kode = 'bagian-' . time();
        }

        $baseKode = $kode;
        $suffix = 2;
        while (BagianLayanan::where('kode', $kode)->where('id', '<>', $item->id)->exists()) {
            $kode = $baseKode . '-' . $suffix;
            $suffix++;
        }

        $item->update([
            'nama' => $nama,
            'kode' => $kode,
        ]);

        return response()->json(['ok' => true, 'data' => $item]);
    }

    public function bagianDestroy($id)
    {
        if (!Schema::hasTable('bagian_layanan')) {
            return response()->json(['ok' => false, 'message' => 'Tabel bagian_layanan belum tersedia.'], 500);
        }

        $item = BagianLayanan::where('is_active', true)->findOrFail($id);

        $hasUsage = SubJenis::where('bagian', $item->id)->exists();
        if ($hasUsage) {
            return response()->json([
                'ok' => false,
                'message' => 'Bagian tidak bisa dihapus karena masih memiliki data sub jenis.',
            ], 422);
        }

        $item->delete();
        return response()->json(['ok' => true]);
    }
}
