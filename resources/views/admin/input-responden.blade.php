@php
    // Kept self-contained (no CDN scripts/styles) so the page also opens without internet.
    $unsur = \App\Support\SurveyOptions::UNSUR;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ea580c">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/assets/logo-bayan.png">
    <title>Input Responden - E-SKM</title>
    <style>
        :root {
            --orange: #ea580c; --orange-soft: #fff7ed; --line: #fed7aa; --text: #1f2937; --muted: #6b7280;
            --ok: #15803d; --ok-bg: #dcfce7; --warn: #b45309; --warn-bg: #fef3c7; --bad: #b91c1c; --bad-bg: #fee2e2;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: var(--text); background: #fffaf5; font-size: 15px; }
        .wrap { max-width: 760px; margin: 0 auto; padding: 0 12px 48px; }
        header.top { position: sticky; top: 0; z-index: 20; background: #fff; border-bottom: 1px solid var(--line); }
        header.top .bar { max-width: 760px; margin: 0 auto; padding: 10px 12px; display: flex; align-items: center; gap: 10px; }
        header.top img { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--line); padding: 2px; }
        header.top h1 { font-size: 16px; margin: 0; flex: 1; }
        header.top a { color: var(--orange); font-weight: 700; font-size: 13px; text-decoration: none; white-space: nowrap; }
        .status { padding: 9px 12px; font-size: 13px; font-weight: 600; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .status .dot { width: 10px; height: 10px; border-radius: 50%; flex: none; }
        .status.online { background: var(--ok-bg); color: var(--ok); } .status.online .dot { background: var(--ok); }
        .status.offline { background: var(--bad-bg); color: var(--bad); } .status.offline .dot { background: var(--bad); }
        .status.session { background: var(--warn-bg); color: var(--warn); } .status.session .dot { background: var(--warn); }
        .status .msg { flex: 1; min-width: 200px; }
        .status button { font-size: 12px; padding: 5px 10px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 14px; margin-top: 14px; }
        .card h2 { font-size: 15px; margin: 0 0 10px; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .full { grid-column: 1 / -1; }
        .f { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
        input[type=text], input[type=tel], input[type=date], input[type=search], select, textarea {
            width: 100%; font: inherit; font-size: 16px; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 9px; background: #fff; color: var(--text);
        }
        textarea { min-height: 64px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: 2px solid #fdba74; border-color: var(--orange); }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chips input { position: absolute; opacity: 0; pointer-events: none; }
        .chips label { border: 1px solid #d1d5db; border-radius: 999px; padding: 7px 12px; font-size: 13px; cursor: pointer; background: #fff; user-select: none; }
        .chips input:checked + label { background: var(--orange); border-color: var(--orange); color: #fff; font-weight: 700; }
        .chips input:focus-visible + label { outline: 2px solid #fdba74; }
        .q { padding: 10px 0; border-top: 1px dashed #f3d9c0; }
        .q:first-child { border-top: 0; }
        .q .t { font-size: 13px; font-weight: 700; margin-bottom: 6px; }
        .q .chips label { font-size: 12px; padding: 6px 10px; }
        .q .chips label b { margin-right: 4px; }
        button { font: inherit; font-weight: 700; border: 0; border-radius: 9px; padding: 10px 16px; cursor: pointer; }
        .btn-main { background: var(--orange); color: #fff; }
        .btn-light { background: #f3f4f6; color: var(--text); }
        .btn-danger { background: var(--bad-bg); color: var(--bad); }
        .actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .actions .btn-main { flex: 1; }
        .editing { background: var(--warn-bg); color: var(--warn); padding: 8px 10px; border-radius: 9px; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
        .flash { padding: 9px 12px; border-radius: 9px; font-size: 13px; font-weight: 600; margin-top: 10px; }
        .flash.ok { background: var(--ok-bg); color: var(--ok); } .flash.warn { background: var(--warn-bg); color: var(--warn); }
        .summary { display: flex; gap: 8px; flex-wrap: wrap; font-size: 12px; margin-bottom: 10px; }
        .summary span { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 999px; padding: 4px 10px; }
        .item { border: 1px solid #f1e3d3; border-radius: 11px; padding: 10px 12px; margin-top: 8px; }
        .item.pending { border-color: #fcd34d; background: #fffdf5; }
        .item.deleting { opacity: .7; }
        .item.deleting .name { text-decoration: line-through; }
        .item .row1 { display: flex; justify-content: space-between; gap: 8px; align-items: flex-start; }
        .item .name { font-weight: 700; }
        .item .meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }
        .badge.ok { background: var(--ok-bg); color: var(--ok); }
        .badge.wait { background: var(--warn-bg); color: var(--warn); }
        .badge.bad { background: var(--bad-bg); color: var(--bad); }
        .item .err { font-size: 12px; color: var(--bad); margin-top: 4px; }
        .item .btns { display: flex; gap: 6px; margin-top: 8px; }
        .item .btns button { padding: 6px 12px; font-size: 13px; }
        .empty { text-align: center; color: var(--muted); font-size: 13px; padding: 18px 0; }
        .hint { font-size: 12px; color: var(--muted); margin-top: 8px; }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="top">
    <div class="bar">
        <img src="/assets/logo-bayan.png" alt="">
        <h1>Input Data Responden</h1>
        <a href="/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun={{ \App\Support\SurveyFilter::year() }}&keterangan={{ urlencode(session('keterangan', 'admin')) }}">← Admin</a>
    </div>
    <div id="status" class="status online" role="status" aria-live="polite">
        <span class="dot"></span><span class="msg" id="statusMsg">Memeriksa koneksi…</span>
        <button type="button" class="btn-light" id="syncBtn">Kirim sekarang</button>
    </div>
</header>

<main class="wrap">
    <section class="card" id="formCard">
        <h2>Form Responden</h2>
        <div id="editingNote" class="editing" hidden></div>
        <form id="form" novalidate>
            <div class="grid">
                <div>
                    <label class="f" for="tanggal">Tanggal survei</label>
                    <input type="date" id="tanggal" name="tanggal" required>
                </div>
                <div>
                    <label class="f" for="bagian">Pilar / Program</label>
                    <select id="bagian" name="bagian" required>
                        @foreach ($bagianOptions as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f" for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" maxlength="150" required autocomplete="off">
                </div>
                <div>
                    <label class="f" for="nohp">No. HP</label>
                    <input type="tel" id="nohp" name="nohp" maxlength="16" inputmode="numeric" autocomplete="off">
                </div>
                <div class="full">
                    <label class="f" for="alamat">Alamat / Desa</label>
                    <input type="text" id="alamat" name="alamat" maxlength="300" autocomplete="off">
                </div>
                <div class="full">
                    <span class="f">Jenis kelamin</span>
                    <div class="chips">
                        @foreach ($jenkelOptions as $i => $opt)
                            <input type="radio" id="jk{{ $i }}" name="jenkel" value="{{ $opt }}" {{ $i === 0 ? 'checked' : '' }}>
                            <label for="jk{{ $i }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="f" for="usia">Usia</label>
                    <select id="usia" name="usia">
                        @foreach ($usiaOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f" for="pendidikan">Pendidikan</label>
                    <select id="pendidikan" name="pendidikan">
                        @foreach ($pendidikanOptions as $opt)
                            <option value="{{ $opt }}" {{ $opt === 'SMA / SMK' ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="full">
                    <label class="f" for="pekerjaan">Pekerjaan</label>
                    <select id="pekerjaan" name="pekerjaan">
                        @foreach ($pekerjaanOptions as $opt)
                            <option value="{{ $opt }}" {{ $opt === 'Swasta' ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-top:14px">
                <span class="f">Penilaian (pilih satu jawaban per unsur)</span>
                @foreach ($unsur as $no => [$judul, $jawaban])
                    <div class="q" data-q="u{{ $no }}">
                        <div class="t">{{ $no }}. {{ $judul }}</div>
                        <div class="chips">
                            @foreach ($jawaban as $idx => $teks)
                                <input type="radio" id="u{{ $no }}-{{ $idx + 1 }}" name="u{{ $no }}" value="{{ $idx + 1 }}">
                                <label for="u{{ $no }}-{{ $idx + 1 }}"><b>{{ $idx + 1 }}</b>{{ $teks }}</label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:10px">
                <label class="f" for="saran">Saran / masukan</label>
                <textarea id="saran" name="saran" maxlength="600"></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn-main" id="submitBtn">Simpan</button>
                <button type="button" class="btn-light" id="cancelEdit" hidden>Batal ubah</button>
            </div>
            <div id="flash" class="flash" hidden></div>
        </form>
    </section>

    <section class="card">
        <h2>Data Responden <button type="button" class="btn-light" id="refreshBtn" style="padding:6px 10px;font-size:12px">Muat ulang</button></h2>
        <div class="summary" id="summary"></div>
        <input type="search" id="search" placeholder="Cari nama atau no. HP…" autocomplete="off">
        <div id="list"></div>
        <p class="hint">Menampilkan 200 data terbaru dari sistem, ditambah data yang belum terkirim dari perangkat ini.
            Agar bisa dipakai tanpa internet, buka halaman ini minimal sekali saat online.</p>
    </section>
</main>

<script>
(() => {
    const API = '/admin/responden';
    const UNSUR = 9;
    const BAGIAN = @json($bagianOptions);
    let csrf = document.querySelector('meta[name=csrf-token]').content;

    // ---------- IndexedDB (data kept on the device) ----------
    // Record shape: { key, serverId, data, state, error, savedAt }
    // state: 'synced' | 'create' | 'update' | 'delete'
    const dbReady = new Promise((resolve, reject) => {
        const req = indexedDB.open('eskm-admin-responden', 1);
        req.onupgradeneeded = () => req.result.createObjectStore('records', { keyPath: 'key' });
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
    const tx = async (mode, fn) => {
        const db = await dbReady;
        return new Promise((resolve, reject) => {
            const t = db.transaction('records', mode);
            const store = t.objectStore('records');
            let result;
            Promise.resolve(fn(store)).then((r) => { result = r; });
            t.oncomplete = () => resolve(result);
            t.onerror = () => reject(t.error);
        });
    };
    const reqP = (r) => new Promise((res, rej) => { r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error); });
    const getAll = () => tx('readonly', (s) => reqP(s.getAll()));
    const getOne = (key) => tx('readonly', (s) => reqP(s.get(key)));
    const put = (rec) => tx('readwrite', (s) => { s.put(rec); });
    const del = (key) => tx('readwrite', (s) => { s.delete(key); });

    const uuid = () => (crypto.randomUUID ? crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        }));
    const today = () => new Date().toLocaleDateString('sv-SE');
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ---------- Elements ----------
    const $ = (id) => document.getElementById(id);
    const form = $('form');
    const statusEl = $('status'), statusMsg = $('statusMsg');
    let online = navigator.onLine;
    let sessionExpired = false;
    let syncing = false;
    let lastSync = null;
    let editingKey = null;

    // ---------- Form ----------
    const resetForm = () => {
        form.reset();
        $('tanggal').value = today();
        editingKey = null;
        $('editingNote').hidden = true;
        $('cancelEdit').hidden = true;
        $('submitBtn').textContent = 'Simpan';
    };
    const readForm = () => {
        const fd = new FormData(form);
        const data = {};
        for (const k of ['tanggal', 'bagian', 'nama', 'nohp', 'alamat', 'jenkel', 'usia', 'pendidikan', 'pekerjaan', 'saran']) {
            data[k] = (fd.get(k) ?? '').toString().trim();
        }
        data.usia = Number(data.usia);
        for (let i = 1; i <= UNSUR; i++) data['u' + i] = fd.get('u' + i) ? Number(fd.get('u' + i)) : null;
        return data;
    };
    const fillForm = (data) => {
        form.reset();
        for (const [k, v] of Object.entries(data)) {
            const els = form.elements[k];
            if (!els) continue;
            if (els instanceof RadioNodeList) {
                for (const el of els) el.checked = String(el.value) === String(v);
            } else {
                els.value = v ?? '';
            }
        }
    };
    const checkForm = (data) => {
        if (!data.tanggal) return ['tanggal', 'Tanggal survei wajib diisi.'];
        if (data.tanggal > today()) return ['tanggal', 'Tanggal survei tidak boleh melebihi hari ini.'];
        if (!data.nama) return ['nama', 'Nama wajib diisi.'];
        for (let i = 1; i <= UNSUR; i++) {
            if (!data['u' + i]) return ['u' + i, 'Unsur ' + i + ' belum dipilih.'];
        }
        return null;
    };
    const flash = (msg, type = 'ok') => {
        const el = $('flash');
        el.textContent = msg; el.className = 'flash ' + type; el.hidden = false;
        clearTimeout(flash.t); flash.t = setTimeout(() => { el.hidden = true; }, 6000);
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = readForm();
        const problem = checkForm(data);
        if (problem) {
            flash(problem[1], 'warn');
            const target = form.querySelector(`[data-q="${problem[0]}"]`) || form.elements[problem[0]];
            (target.scrollIntoView ? target : target[0]).scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (editingKey) {
            const rec = await getOne(editingKey);
            if (rec) {
                rec.data = { ...rec.data, ...data };
                if (rec.state === 'synced') rec.state = 'update';
                rec.error = null;
                rec.savedAt = Date.now();
                await put(rec);
            }
        } else {
            const key = uuid();
            await put({ key, serverId: null, data: { ...data, client_uuid: key }, state: 'create', error: null, savedAt: Date.now() });
        }

        const wasEdit = !!editingKey;
        resetForm();
        await render();
        flash(online && !sessionExpired
            ? (wasEdit ? 'Perubahan disimpan, sedang dikirim ke sistem…' : 'Data disimpan, sedang dikirim ke sistem…')
            : 'Data disimpan di perangkat ini. Akan dikirim otomatis saat internet tersedia.', online && !sessionExpired ? 'ok' : 'warn');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        sync();
    });
    $('cancelEdit').addEventListener('click', resetForm);

    // ---------- List ----------
    const avg = (d) => {
        const vals = Array.from({ length: UNSUR }, (_, i) => Number(d['u' + (i + 1)]) || 0).filter(Boolean);
        return vals.length ? (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(2) : '-';
    };
    const fmtDate = (s) => s ? new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
    const badge = (rec) => {
        if (rec.state === 'synced') return '<span class="badge ok">✓ Tersimpan di sistem</span>';
        if (rec.state === 'delete') return '<span class="badge wait">Menunggu dihapus</span>';
        if (rec.error) return '<span class="badge bad">Gagal dikirim</span>';
        return '<span class="badge wait">Belum terkirim (di perangkat)</span>';
    };

    async function render() {
        const all = await getAll();
        const q = $('search').value.trim().toLowerCase();
        const pending = all.filter((r) => r.state !== 'synced');
        const rows = all
            .filter((r) => !q || (r.data.nama || '').toLowerCase().includes(q) || (r.data.nohp || '').includes(q))
            .sort((a, b) => (a.state === 'synced') - (b.state === 'synced')
                || (b.data.tanggal || '').localeCompare(a.data.tanggal || '')
                || (b.savedAt || 0) - (a.savedAt || 0));

        $('summary').innerHTML = `<span>Total: <b>${all.length}</b></span>`
            + `<span>Tersimpan di sistem: <b>${all.length - pending.length}</b></span>`
            + `<span style="${pending.length ? 'background:#fef3c7;border-color:#fcd34d' : ''}">Belum terkirim: <b>${pending.length}</b></span>`;

        $('list').innerHTML = rows.length ? rows.map((r) => `
            <div class="item ${r.state === 'synced' ? '' : 'pending'} ${r.state === 'delete' ? 'deleting' : ''}">
                <div class="row1">
                    <div>
                        <div class="name">${esc(r.data.nama || '(tanpa nama)')}</div>
                        <div class="meta">${fmtDate(r.data.tanggal)} · ${esc(BAGIAN[r.data.bagian] || r.data.bagian || 'Tanpa pilar')} · Rata-rata ${avg(r.data)}</div>
                    </div>
                    ${badge(r)}
                </div>
                ${r.error ? `<div class="err">${esc(r.error)}</div>` : ''}
                <div class="btns">
                    ${r.state === 'delete'
                        ? `<button type="button" class="btn-light" data-act="undo" data-key="${esc(r.key)}">Batal hapus</button>`
                        : `<button type="button" class="btn-light" data-act="edit" data-key="${esc(r.key)}">Ubah</button>
                           <button type="button" class="btn-danger" data-act="delete" data-key="${esc(r.key)}">Hapus</button>`}
                </div>
            </div>`).join('') : `<div class="empty">${q ? 'Tidak ada data yang cocok.' : 'Belum ada data.'}</div>`;

        updateStatus(pending.length);
    }

    $('list').addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const rec = await getOne(btn.dataset.key);
        if (!rec) return render();

        if (btn.dataset.act === 'edit') {
            editingKey = rec.key;
            fillForm(rec.data);
            $('editingNote').textContent = 'Mode ubah: ' + (rec.data.nama || '(tanpa nama)');
            $('editingNote').hidden = false;
            $('cancelEdit').hidden = false;
            $('submitBtn').textContent = 'Simpan perubahan';
            $('formCard').scrollIntoView({ behavior: 'smooth' });
        }
        if (btn.dataset.act === 'delete') {
            if (!confirm('Hapus data "' + (rec.data.nama || '') + '"?')) return;
            if (editingKey === rec.key) resetForm();
            if (rec.state === 'create' && !syncing) {
                await del(rec.key); // never reached the server, just drop it
            } else {
                rec.state = 'delete'; rec.error = null; rec.savedAt = Date.now();
                await put(rec);
            }
            await render();
            sync();
        }
        if (btn.dataset.act === 'undo') {
            // Re-send current data so the server copy stays as shown.
            rec.state = rec.serverId ? 'update' : 'create'; rec.error = null; rec.savedAt = Date.now();
            await put(rec);
            await render();
            sync();
        }
    });
    $('search').addEventListener('input', () => render());

    // ---------- Status bar ----------
    function updateStatus(pendingCount) {
        const n = pendingCount;
        let cls = 'online', msg;
        if (sessionExpired) {
            cls = 'session';
            msg = `Sesi login berakhir. ${n} data belum terkirim tetapi aman di perangkat ini. Silakan login ulang, lalu buka halaman ini lagi untuk mengirim.`;
        } else if (!online) {
            cls = 'offline';
            msg = `Sedang OFFLINE. ${n ? n + ' data belum terkirim, tersimpan aman di perangkat ini dan' : 'Data baru akan disimpan di perangkat ini dan'} dikirim otomatis saat internet kembali.`;
        } else if (syncing && n) {
            msg = `Online. Mengirim ${n} data ke sistem…`;
        } else if (n) {
            cls = 'session';
            msg = `Online, tetapi ${n} data belum terkirim. Lihat keterangan pada data bertanda merah/kuning.`;
        } else {
            msg = 'Online. Semua data sudah tersimpan di sistem.' + (lastSync ? ` (cek terakhir ${lastSync.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})` : '');
        }
        statusEl.className = 'status ' + cls;
        statusMsg.textContent = msg;
        $('syncBtn').hidden = !n || syncing;
    }
    const setOnline = (value) => { online = value; render(); };

    // ---------- Server sync ----------
    class SessionError extends Error {}
    async function request(method, url, body) {
        const res = await fetch(url, {
            method,
            credentials: 'same-origin',
            redirect: 'manual', // a redirect here means the login session ended
            cache: 'no-store',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : undefined,
        });
        if (res.type === 'opaqueredirect' || res.status === 401) throw new SessionError();
        let json = null;
        try { json = await res.json(); } catch (_) {}
        return { status: res.status, ok: res.ok, json };
    }
    const errorText = (r) => {
        if (r.status === 422 && r.json?.errors) return 'Data tidak valid: ' + Object.values(r.json.errors).flat()[0];
        if (r.json?.message) return r.json.message;
        return 'Server bermasalah (kode ' + r.status + '). Akan dicoba lagi.';
    };

    async function sendOne(rec) {
        const startedAt = rec.savedAt;
        let r;
        if (rec.state === 'create') r = await request('POST', API, rec.data);
        else if (rec.state === 'update') r = await request('PUT', `${API}/${rec.serverId}`, rec.data);
        else if (rec.state === 'delete') {
            if (!rec.serverId) { await del(rec.key); return; }
            r = await request('DELETE', `${API}/${rec.serverId}`);
        }
        if (r.status === 419) throw new SessionError('csrf');

        const cur = await getOne(rec.key);
        if (!r.ok) {
            if (cur) { cur.error = errorText(r); await put(cur); }
            return;
        }
        if (rec.state === 'delete') { await del(rec.key); return; }

        const serverRow = r.json.data;
        if (!cur) {
            // Deleted on the device while it was being sent: remove it from the server too.
            await put({ key: rec.key, serverId: serverRow.id, data: serverRow, state: 'delete', error: null, savedAt: Date.now() });
        } else if (cur.savedAt !== startedAt) {
            // Changed on the device while it was being sent: keep the newer local version.
            cur.serverId = serverRow.id;
            if (cur.state === 'create') cur.state = 'update';
            await put(cur);
        } else {
            await put({ key: rec.key, serverId: serverRow.id, data: serverRow, state: 'synced', error: null, savedAt: Date.now() });
        }
    }

    async function refreshToken() {
        const r = await request('GET', API + '/token');
        if (r.ok && r.json?.token) csrf = r.json.token;
    }

    async function pullFromServer() {
        const r = await request('GET', API);
        if (!r.ok) return;
        const local = await getAll();
        const pendingKeys = new Set(local.filter((x) => x.state !== 'synced').map((x) => x.key));
        const pendingIds = new Set(local.filter((x) => x.state !== 'synced' && x.serverId).map((x) => x.serverId));
        await tx('readwrite', (s) => {
            for (const x of local) if (x.state === 'synced') s.delete(x.key);
            for (const row of r.json.data) {
                const key = row.client_uuid || ('srv-' + row.id);
                if (pendingKeys.has(key) || pendingIds.has(row.id)) continue;
                s.put({ key, serverId: row.id, data: row, state: 'synced', error: null, savedAt: 0 });
            }
        });
    }

    async function sync({ pull = true } = {}) {
        if (syncing) return;
        syncing = true;
        await render();
        try {
            await refreshToken(); // also tells us if we are online and still logged in
            setOnline(true);
            sessionExpired = false;
            const queue = (await getAll()).filter((r) => r.state !== 'synced').sort((a, b) => a.savedAt - b.savedAt);
            for (const rec of queue) {
                try {
                    await sendOne(rec);
                } catch (err) {
                    if (err instanceof SessionError && err.message === 'csrf') {
                        await refreshToken();
                        await sendOne(rec);
                    } else {
                        throw err;
                    }
                }
            }
            if (pull) await pullFromServer();
            lastSync = new Date();
        } catch (err) {
            if (err instanceof SessionError) sessionExpired = true;
            else online = false; // network failure: treat as offline
        } finally {
            syncing = false;
            await render();
        }
    }

    $('syncBtn').addEventListener('click', () => sync());
    $('refreshBtn').addEventListener('click', () => sync());
    window.addEventListener('online', () => { online = true; sync(); });
    window.addEventListener('offline', () => setOnline(false));
    // Keep trying in the background; navigator.onLine is not reliable on weak networks.
    setInterval(() => { if (!syncing) sync({ pull: !online }); }, 30000);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') sync(); });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    resetForm();
    render().then(() => sync());
})();
</script>
</body>
</html>
