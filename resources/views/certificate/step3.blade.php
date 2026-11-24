<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan Nomor Sertifikat | ProCert</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    @include('partials.wizard-header', ['currentStep' => 3])
    <main class="number-page number-builder-page">
        <header class="number-heading">
            <h1>Pengaturan Nomor Sertifikat</h1>
            <p>Rancang format nomor sertifikat sesuai kebutuhan. Setiap bagian dapat diatur sendiri dan digunakan kembali.</p>
        </header>

        <form id="certificateNumberForm" method="POST" action="{{ route('certificate-number.store') }}">
            @csrf
            <input type="hidden" name="fields" id="fieldsPayload">
            <input type="hidden" name="separator" id="separatorPayload" value="/">
            <input type="hidden" name="format_name" id="formatNamePayload" value="Format Baru">

            <section class="number-builder-layout">
                <div class="number-builder-preview-column">
                    <div class="number-builder-preview-card">
                        <div class="number-builder-section-heading">
                            <span class="number-builder-kicker">LIVE PREVIEW</span>
                            <span class="number-builder-status" id="previewStatus">Belum diterapkan</span>
                        </div>
                        <div class="number-builder-preview-canvas">
                            <span class="number-builder-preview-dash" id="previewCode">—</span>
                        </div>
                        <p class="number-builder-preview-hint" id="previewHint">Preview akan muncul setelah kamu menekan “Gunakan Format”.</p>
                    </div>

                    <section class="number-saved-formats">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">LIBRARY FORMAT</span>
                                <h2>Format tersimpan</h2>
                            </div>
                            <span class="number-saved-count" id="savedFormatCount">{{ count($savedFormats) }} format</span>
                        </div>
                        <label class="number-format-search">
                            <span aria-hidden="true">⌕</span>
                            <input id="savedFormatSearch" type="search" placeholder="Cari format yang pernah disimpan...">
                        </label>
                        <div class="number-saved-list" id="savedFormatList">
                            @forelse ($savedFormats as $format)
                                <button type="button" class="number-saved-item"
                                    data-format-id="{{ $format->id }}"
                                    data-format-name="{{ $format->name }}"
                                    data-fields='@json($format->fields)'
                                    data-separator="{{ $format->separator }}">
                                    <span class="number-saved-item-icon">▤</span>
                                    <span class="number-saved-item-copy">
                                        <strong>{{ $format->name }}</strong>
                                        <small>{{ count($format->fields) }} bagian · {{ implode(' / ', array_column($format->fields, 'label')) }}</small>
                                    </span>
                                    <span class="number-saved-item-arrow">→</span>
                                </button>
                            @empty
                                <div class="number-saved-empty" id="savedFormatEmpty">Belum ada format tersimpan. Simpan format pertamamu untuk menggunakannya lagi nanti.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="number-builder-controls">
                    <div id="formatBuilderStage">
                    <section class="number-builder-card">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">FORMAT BUILDER</span>
                                <h2>Susun bagian nomor</h2>
                            </div>
                            <span class="number-builder-count" id="fieldCount">0/12</span>
                        </div>
                        <p class="number-builder-description">Susun nomor sertifikat sesuai kebutuhan. Setiap bagian yang kamu tambahkan akan menjadi satu kolom isian.</p>

                        <div class="number-builder-fields" id="builderFields"></div>
                        <button type="button" class="number-add-field" id="addFieldButton">
                            <span>＋</span> Tambah kolom format
                        </button>

                        <div class="number-builder-options">
                            <label for="formatName">Nama format</label>
                            <input id="formatName" type="text" maxlength="120" placeholder="Contoh: Format Sertifikat Prodi">
                            <label for="fieldSeparator">Pemisah antarbagian</label>
                            <input id="fieldSeparator" type="text" maxlength="20" value="/" placeholder="/">
                            <small>Contoh pemisah: /, -, |, atau spasi.</small>
                        </div>

                    </section>

                    <section class="number-builder-card number-builder-action-card">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">AKSI FORMAT</span>
                                <h2>Simpan atau gunakan</h2>
                            </div>
                        </div>
                        <p class="number-builder-description">Simpan rancangan agar bisa dipakai lagi, atau langsung terapkan untuk sertifikat ini.</p>
                        <div class="number-builder-actions">
                            <button type="button" class="number-save-format" id="saveFormatButton">
                                <span>♡</span> Save Format
                            </button>
                            <button type="button" class="number-use-format" id="useFormatButton">
                                Gunakan Format <span>→</span>
                            </button>
                        </div>
                        <p class="number-builder-feedback" id="builderFeedback" role="status"></p>
                    </section>
                    </div>

                    <section class="number-builder-card number-builder-fill-card" id="fillFieldsCard" hidden>
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">FORMAT AKTIF</span>
                                <h2 id="activeFormatTitle">Isi kolom format</h2>
                            </div>
                            <span class="number-active-indicator">Aktif</span>
                        </div>
                        <p class="number-builder-description">Hanya bagian yang kamu tambahkan ke format yang ditampilkan di sini. Isi setiap kolom sesuai kebutuhan sertifikat.</p>
                        <div class="number-active-fields" id="activeFields"></div>
                        <button type="button" class="number-edit-format" id="editFormatButton">
                            ← Ubah susunan format
                        </button>
                        {{-- Nilai sistem disimpan tersembunyi. Kolom hanya muncul jika user menambahkannya di Format Builder. --}}
                        <input type="hidden" id="startNumber" name="start_number" value="{{ old('start_number', $initialStartNumber) }}">
                        <input type="hidden" id="month" name="month" value="{{ old('month', $selectedMonth) }}">
                        <input type="hidden" id="year" name="year" value="{{ old('year', $selectedYear) }}">
                    </section>
                </aside>
            </section>

            <section class="number-reminder">
                <div class="number-reminder-icon" aria-hidden="true">↻</div>
                <div>
                    <h2>Pengingat Nomor Sertifikat</h2>
                    <p>Nomor terakhir yang tercatat pada tahun {{ $selectedYear }} adalah {{ str_pad((string) $lastNumber, 3, '0', STR_PAD_LEFT) }}. Nomor awal yang disarankan adalah {{ str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT) }}.</p>
                </div>
            </section>

            <div id="numberNotice" class="number-notice" role="status" aria-live="polite"></div>
            <footer class="number-actions">
                <a class="number-button" href="{{ route('recipients.index', ['back' => 1]) }}">← Kembali</a>
                <button class="number-button number-button-primary" id="continueButton" type="submit" disabled>Lanjut →</button>
            </footer>
        </form>
    </main>
</div>

<script>
(() => {
    const romanMonths = {1:'I',2:'II',3:'III',4:'IV',5:'V',6:'VI',7:'VII',8:'VIII',9:'IX',10:'X',11:'XI',12:'XII'};
    const monthNames = {1:'Januari',2:'Februari',3:'Maret',4:'April',5:'Mei',6:'Juni',7:'Juli',8:'Agustus',9:'September',10:'Oktober',11:'November',12:'Desember'};
    const types = [
        ['text','Teks bebas'], ['number','Nomor urut'], ['month','Bulan angka'],
        ['roman_month','Bulan Romawi'], ['month_name','Nama bulan'], ['year','Tahun']
    ];
    const builder = document.getElementById('builderFields');
    const activeFields = document.getElementById('activeFields');
    const fieldsPayload = document.getElementById('fieldsPayload');
    const separatorInput = document.getElementById('fieldSeparator');
    const preview = document.getElementById('previewCode');
    const continueButton = document.getElementById('continueButton');
    const fillCard = document.getElementById('fillFieldsCard');
    const builderStage = document.getElementById('formatBuilderStage');
    const feedback = document.getElementById('builderFeedback');
    const savedList = document.getElementById('savedFormatList');
    let fields = [];
    let applied = false;

    const oldSettings = @json($numberSettings ?? []);
    const savedFormats = @json($savedFormats->values());

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    }
    function makeField(field = {}) {
        return {
            id: field.id || `field-${Math.random().toString(36).slice(2,9)}`,
            label: field.label || '',
            type: field.type || 'text',
            value: field.value || ''
        };
    }
    function fieldTypeOptions(selected) {
        return types.map(([value,label]) => `<option value="${value}" ${value===selected?'selected':''}>${label}</option>`).join('');
    }
    function renderBuilder() {
        builder.innerHTML = '';
        fields.forEach((field,index) => {
            const card = document.createElement('div');
            card.className = 'number-builder-field';
            card.innerHTML = `
                <div class="number-builder-field-top">
                    <span class="number-builder-index">${String(index+1).padStart(2,'0')}</span>
                    <button type="button" class="number-remove-field" aria-label="Hapus bagian">×</button>
                </div>
                <label>Judul kolom</label>
                <input class="builder-label" type="text" maxlength="80" placeholder="Contoh: Kode unit atau jenis dokumen" value="${escapeHtml(field.label)}">
                <label>Isi / jenis teks</label>
                <select class="builder-type">${fieldTypeOptions(field.type)}</select>
                <input class="builder-value" type="text" maxlength="120" placeholder="Contoh: PP05" value="${escapeHtml(field.value)}" ${field.type!=='text'?'hidden':''}>
                <small>${field.type==='text'?'Teks ini akan menjadi isi kolom saat format digunakan.':'Nilai akan diisi otomatis dari pengaturan nomor sertifikat.'}</small>`;
            card.querySelector('.builder-label').addEventListener('input', e => { field.label=e.target.value; });
            card.querySelector('.builder-type').addEventListener('change', e => { field.type=e.target.value; renderBuilder(); });
            card.querySelector('.builder-value').addEventListener('input', e => { field.value=e.target.value; });
            card.querySelector('.number-remove-field').addEventListener('click', () => {
                fields.splice(index,1); renderBuilder();
            });
            builder.appendChild(card);
        });
        document.getElementById('fieldCount').textContent = `${fields.length}/12`;
        document.getElementById('addFieldButton').disabled = fields.length >= 12;
    }
    function currentValue(field) {
        if (field.type==='number') return String(document.getElementById('startNumber').value || 1).padStart(3,'0');
        if (field.type==='month') return document.getElementById('month').value || '';
        if (field.type==='roman_month') return romanMonths[Number(document.getElementById('month').value)] || '';
        if (field.type==='month_name') return monthNames[Number(document.getElementById('month').value)] || '';
        if (field.type==='year') return document.getElementById('year').value || '';
        const input = activeFields.querySelector(`[data-field-id="${field.id}"]`);
        return input ? input.value : field.value;
    }
    function renderPreview() {
        if (!applied) { preview.textContent='—'; return; }
        const values = fields.map(currentValue);
        preview.textContent = values.join(separatorInput.value);
    }
    function renderActiveFields() {
        activeFields.innerHTML = '';
        fields.forEach((field,index) => {
            const wrap=document.createElement('div');
            wrap.className='number-active-field';
            const auto=field.type!=='text';
            const automaticValue = field.type==='number'
                ? String(document.getElementById('startNumber').value || 1).padStart(3,'0')
                : field.type==='month' ? (document.getElementById('month').value || '')
                : field.type==='roman_month' ? (romanMonths[Number(document.getElementById('month').value)] || '')
                : field.type==='month_name' ? (monthNames[Number(document.getElementById('month').value)] || '')
                : field.type==='year' ? (document.getElementById('year').value || '')
                : field.value;
            wrap.innerHTML=`<label>${escapeHtml(field.label || `Kolom ${index+1}`)}</label>
                <input type="text" data-field-id="${field.id}" value="${escapeHtml(automaticValue)}" ${auto?'readonly':''} placeholder="Isi ${escapeHtml(field.label || 'teks')}">
                ${auto?'<small>Terisi otomatis dari pengaturan nomor.</small>':''}`;
            const input=wrap.querySelector('input');
            input.addEventListener('input',renderPreview);
            activeFields.appendChild(wrap);
        });
    }
    function applyFormat() {
        if (!fields.length || fields.some(f=>!f.label.trim())) {
            feedback.textContent='Tambahkan minimal satu bagian dan isi semua judul kolom terlebih dahulu.';
            feedback.classList.add('is-error'); return;
        }
        applied=true;
        builderStage.hidden=true;
        fillCard.hidden=false;
        document.getElementById('activeFormatTitle').textContent=document.getElementById('formatName').value.trim() || 'Isi kolom format';
        renderActiveFields();
        continueButton.disabled=false;
        document.getElementById('previewStatus').textContent='Format diterapkan';
        document.getElementById('previewHint').textContent='Preview diperbarui otomatis saat isi kolom atau pengaturan nomor berubah.';
        feedback.textContent='Format berhasil diterapkan. Isi kolom di bawah untuk memperbarui preview.';
        feedback.classList.remove('is-error');
        syncPayload(); renderPreview();
    }
    function syncPayload() {
        fields.forEach(field => {
            if (field.type === 'text') field.value = currentValue(field);
        });
        const payload=fields.map(f=>({...f, value: currentValue(f)}));
        fieldsPayload.value=JSON.stringify(payload);
        document.getElementById('separatorPayload').value=separatorInput.value;
        document.getElementById('formatNamePayload').value=document.getElementById('formatName').value.trim() || 'Format Baru';
    }
    function renderSavedFormats(items) {
        savedList.innerHTML='';
        if (!items.length) {
            savedList.innerHTML='<div class="number-saved-empty">Belum ada format tersimpan.</div>'; return;
        }
        items.forEach(format=>{
            const button=document.createElement('button');
            button.type='button'; button.className='number-saved-item'; button.dataset.formatId=String(format.id);
            button.innerHTML=`<span class="number-saved-item-icon">▤</span><span class="number-saved-item-copy"><strong>${escapeHtml(format.name)}</strong><small>${format.fields.length} bagian · ${format.fields.map(f=>escapeHtml(f.label)).join(' / ')}</small></span><span class="number-saved-item-arrow">→</span>`;
            button.addEventListener('click',()=>{
                fields=format.fields.map(makeField);
                separatorInput.value=format.separator || '/';
                document.getElementById('formatName').value=format.name;
                renderBuilder(); applyFormat();
            });
            savedList.appendChild(button);
        });
    }
    document.getElementById('addFieldButton').addEventListener('click',()=>{
        if(fields.length>=12)return;
        fields.push(makeField()); renderBuilder();
    });
    document.getElementById('useFormatButton').addEventListener('click',applyFormat);
    document.getElementById('editFormatButton').addEventListener('click',()=>{
        applied=false;
        fillCard.hidden=true;
        builderStage.hidden=false;
        continueButton.disabled=true;
        document.getElementById('previewStatus').textContent='Belum diterapkan';
        document.getElementById('previewHint').textContent='Preview akan muncul setelah kamu menekan “Gunakan Format”.';
        preview.textContent='—';
        feedback.textContent='Silakan ubah susunan format, lalu gunakan kembali.';
        feedback.classList.remove('is-error');
    });
    document.getElementById('saveFormatButton').addEventListener('click',async()=>{
        if(!fields.length || fields.some(f=>!f.label.trim())) {
            feedback.textContent='Isi judul setiap kolom sebelum menyimpan format.';
            feedback.classList.add('is-error'); return;
        }
        const name=document.getElementById('formatName').value.trim();
        if(!name){feedback.textContent='Beri nama format terlebih dahulu.';feedback.classList.add('is-error');return;}
        const payload={name,separator:separatorInput.value,fields:fields.map(f=>({...f}))};
        try {
            const response=await fetch(@json(route('certificate-number.formats.store')),{
                method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body:JSON.stringify(payload)
            });
            const result=await response.json();
            if(!response.ok) throw new Error(result.message || 'Format gagal disimpan.');
            const existing=savedList.querySelector('.number-saved-empty'); if(existing)existing.remove();
            const item=result.format; savedFormats.unshift(item);
            const button=document.createElement('button');button.type='button';button.className='number-saved-item';button.dataset.formatId=String(item.id);
            button.innerHTML=`<span class="number-saved-item-icon">▤</span><span class="number-saved-item-copy"><strong>${escapeHtml(item.name)}</strong><small>${item.fields.length} bagian · ${item.fields.map(f=>escapeHtml(f.label)).join(' / ')}</small></span><span class="number-saved-item-arrow">→</span>`;
            button.addEventListener('click',()=>{fields=item.fields.map(makeField);separatorInput.value=item.separator||'/';document.getElementById('formatName').value=item.name;renderBuilder();applyFormat();});
            savedList.prepend(button);
            document.getElementById('savedFormatCount').textContent=`${savedList.children.length} format`;
            feedback.textContent='Format berhasil disimpan ke database.';
            feedback.classList.remove('is-error');
        } catch(error) {feedback.textContent=error.message;feedback.classList.add('is-error');}
    });
    document.getElementById('savedFormatSearch').addEventListener('input',e=>{
        const query=e.target.value.toLowerCase();
        [...savedList.children].forEach(item=>item.hidden=!item.textContent.toLowerCase().includes(query));
    });
    savedList.addEventListener('click',event=>{
        const button=event.target.closest('.number-saved-item');
        if(!button) return;
        const item=savedFormats.find(format=>String(format.id)===button.dataset.formatId);
        if(!item) return;
        fields=item.fields.map(makeField);
        separatorInput.value=item.separator || '/';
        document.getElementById('formatName').value=item.name;
        renderBuilder(); applyFormat();
    });
    [separatorInput,document.getElementById('startNumber'),document.getElementById('month'),document.getElementById('year')].forEach(el=>{
        el.addEventListener('input',()=>{if(applied){syncPayload();renderActiveFields();syncPayload();renderPreview();}});
        el.addEventListener('change',()=>{if(applied){syncPayload();renderActiveFields();syncPayload();renderPreview();}});
    });
    activeFields.addEventListener('input',()=>{if(applied){syncPayload();renderPreview();}});
    document.getElementById('certificateNumberForm').addEventListener('submit',e=>{
        if(!applied){e.preventDefault();feedback.textContent='Gunakan format terlebih dahulu sebelum melanjutkan.';feedback.classList.add('is-error');return;}
        syncPayload();
    });

    // Start intentionally empty: user must build or load a format first.
    fields=[];
    renderBuilder();
    renderSavedFormats(savedFormats);
    continueButton.disabled=true;
    if (Array.isArray(oldSettings.fields) && oldSettings.fields.length) {
        fields=oldSettings.fields.map(makeField);
        separatorInput.value=oldSettings.separator || '/';
        document.getElementById('formatName').value=oldSettings.format_name || '';
        renderBuilder();
        applyFormat();
    }
})();
</script>
</body>
</html>
