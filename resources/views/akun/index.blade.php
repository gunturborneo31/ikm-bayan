@extends('layouts.index', ['title' => 'Akun Pengguna'])

@php
    $input = 'rounded-lg border-orange-200 text-sm px-2 py-1.5';
    $btn = 'no-theme rounded-lg px-3 py-1.5 text-xs font-bold';
@endphp

@section('content')
<div class="admin-main">
    <div class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-orange-800">Akun Pengguna</h2>
            <p class="text-sm text-gray-600">
                <b>Admin</b>: semua fitur. <b>Surveyor</b>: semua fitur kecuali Master Data PPM dan Akun Pengguna.
                Setiap data yang diinput lewat Input Responden{{ config('skm.import_excel') ? ' atau Import Excel' : '' }} tercatat atas nama akun penginputnya.
            </p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-300 text-green-800 font-semibold text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ $errors->first() }}</div>
        @endif

        {{-- Tambah akun --}}
        <details class="rounded-xl border border-orange-200 bg-orange-50/50 p-4" {{ $errors->any() && ! old('_edit') ? 'open' : '' }}>
            <summary class="cursor-pointer font-extrabold text-gray-900">+ Tambah akun</summary>
            <form method="POST" action="{{ route('akun.store') }}" class="mt-3 grid gap-3 md:grid-cols-3" autocomplete="off">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600">Nama</label>
                    <input name="name" value="{{ old('_edit') ? '' : old('name') }}" required maxlength="100" class="{{ $input }} w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Username (untuk login)</label>
                    <input name="username" value="{{ old('_edit') ? '' : old('username') }}" required maxlength="50" placeholder="mis. surveyor.tabang" class="{{ $input }} w-full lowercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Role</label>
                    <select name="keterangan" class="{{ $input }} w-full">
                        @foreach ($roles as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('keterangan', 'surveyor') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Password (min. 8 karakter)</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password" class="{{ $input }} w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Ulangi password</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="{{ $input }} w-full">
                </div>
                <div class="flex items-end">
                    <button class="{{ $btn }} bg-orange-600 text-white hover:bg-orange-700 px-4 py-2">Simpan akun</button>
                </div>
            </form>
        </details>

        {{-- Daftar akun --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Nama & username</th>
                        <th class="text-left">Role</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Data diinput</th>
                        <th class="text-left">Login terakhir</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akun as $a)
                        @php
                            $input_n = $jumlahInput[$a->id]->n ?? 0;
                            $sendiri = $a->id === $saya;
                        @endphp
                        <tr class="{{ $a->is_active ? '' : 'text-gray-400' }}">
                            <td>
                                <b>{{ $a->name }}</b> @if ($sendiri)<span class="text-[10px] font-black text-orange-700">(Anda)</span>@endif
                                <div class="text-xs text-gray-500">{{ $a->username }}</div>
                            </td>
                            <td>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ \App\Support\Akses::isAdmin($a->keterangan) ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ \App\Support\Akses::roleLabel($a->keterangan) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($a->is_active)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-bold text-green-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-bold text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($input_n)
                                    <a class="js-admin-nav font-bold text-orange-700 hover:underline" href="/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&penginput={{ $a->id }}&Tahun={{ \App\Support\SurveyFilter::year() }}">{{ number_format($input_n) }}</a>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-xs">{{ $a->last_login_at ? $a->last_login_at->locale('id')->translatedFormat('d M Y H:i') : 'Belum pernah' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" class="{{ $btn }} border border-orange-300 text-orange-800 hover:bg-orange-50" data-edit="{{ $a->id }}">Ubah</button>
                            </td>
                        </tr>
                        <tr id="edit-{{ $a->id }}" hidden>
                            <td colspan="6" class="bg-orange-50/60">
                                <form method="POST" action="{{ route('akun.update', $a->id) }}" class="grid gap-2 md:grid-cols-4 p-2" autocomplete="off">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="_edit" value="{{ $a->id }}">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600">Nama</label>
                                        <input name="name" value="{{ $a->name }}" required maxlength="100" class="{{ $input }} w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600">Username</label>
                                        <input name="username" value="{{ $a->username }}" required maxlength="50" class="{{ $input }} w-full lowercase">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600">Role</label>
                                        <select name="keterangan" class="{{ $input }} w-full" @disabled($sendiri)>
                                            @foreach ($roles as $kode => $label)
                                                <option value="{{ $kode }}" @selected(\App\Support\Akses::isAdmin($a->keterangan) ? $kode === 'admin' : $kode === $a->keterangan)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @if ($sendiri)<input type="hidden" name="keterangan" value="admin">@endif
                                    </div>
                                    <div class="flex items-end">
                                        <label class="flex items-center gap-2 text-sm font-semibold">
                                            <input type="hidden" name="is_active" value="{{ $sendiri ? 1 : 0 }}">
                                            <input type="checkbox" name="is_active" value="1" @checked($a->is_active) @disabled($sendiri) class="rounded border-orange-300 text-orange-600">
                                            Akun aktif
                                        </label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600">Password baru (kosongkan bila tidak diganti)</label>
                                        <input type="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }} w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600">Ulangi password baru</label>
                                        <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" class="{{ $input }} w-full">
                                    </div>
                                    <div class="flex items-end gap-2 md:col-span-2">
                                        <button class="{{ $btn }} bg-orange-600 text-white hover:bg-orange-700 px-4 py-2">Simpan perubahan</button>
                                        <button type="button" class="{{ $btn }} border border-gray-300 text-gray-700 px-4 py-2" data-edit="{{ $a->id }}">Batal</button>
                                    </div>
                                </form>
                                @unless ($sendiri)
                                    <form method="POST" action="{{ route('akun.destroy', $a->id) }}" class="no-loading px-2 pb-2"
                                          onsubmit="return confirm('Hapus akun {{ $a->name }}? Akun yang sudah menginput data tidak bisa dihapus, cukup dinonaktifkan.')">
                                        @csrf @method('DELETE')
                                        <button class="{{ $btn }} border border-red-300 text-red-700 hover:bg-red-50">Hapus akun</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = document.getElementById('edit-' + btn.dataset.edit);
            row.hidden = !row.hidden;
        });
    });
    @if (old('_edit'))
        document.getElementById('edit-{{ old('_edit') }}')?.removeAttribute('hidden');
    @endif
</script>
@endsection
