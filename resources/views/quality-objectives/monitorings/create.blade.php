@extends('layouts.iso')

@section('title', 'Tambah Data Pemantauan')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Input Realisasi Pemantauan</h1>
            <p class="sub">Sasaran Mutu: {{ $objective->code }} - {{ $objective->process_name }}</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>

<div class="card card-section card-inner" style="padding: 24px; max-width: 600px; margin: 0 auto;">
    <form action="{{ route('quality-objectives.monitorings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="objective_id" value="{{ $objective->id }}">
        <input type="hidden" name="period_year" value="{{ $objective->period->year }}">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Period Label --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Label Periode Lapor <span style="color: var(--error);">*</span></label>
                <input type="text" name="period_label" class="form-input @error('period_label') is-invalid @enderror" value="{{ old('period_label', date('Y-m')) }}" placeholder="Contoh: {{ date('Y-m') }} atau Q1" required>
                @error('period_label')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Target Snapshot --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Target Snapshot <span style="color: var(--error);">*</span></label>
                <input type="number" step="0.01" name="target_snapshot" class="form-input" value="{{ old('target_snapshot', $objective->target_value) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Bulan (Month) --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Bulan Ke (1-12)</label>
                <input type="number" name="period_month" class="form-input" value="{{ old('period_month', date('n')) }}" min="1" max="12">
            </div>

            {{-- Kuartal (Quarter) --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Kuartal Ke (1-4)</label>
                <input type="number" name="period_quarter" class="form-input" value="{{ old('period_quarter') }}" min="1" max="4" placeholder="Diisi jika triwulan">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Realization Value --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nilai Realisasi <span style="color: var(--error);">*</span></label>
                <input type="number" step="0.01" name="realization_value" class="form-input @error('realization_value') is-invalid @enderror" value="{{ old('realization_value') }}" placeholder="Contoh: 96.5" required min="0">
                @error('realization_value')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Sumber Data --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Sumber Data / Dokumen Acuan</label>
                <input type="text" name="data_source" class="form-input" value="{{ old('data_source') }}" placeholder="Contoh: Laporan Penjualan Harian">
            </div>
        </div>

        {{-- Notes / Analysis --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Analisis & Catatan Hambatan</label>
            <textarea name="notes" class="form-textarea" rows="3" placeholder="Tuliskan analisis mengapa target tercapai / tidak tercapai..."></textarea>
        </div>

        {{-- Evidence File --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Unggah Bukti Kinerja (Evidence Upload)</label>
            <input type="file" name="evidence_file" class="form-input" style="padding: 8px;">
            <span class="small-muted" style="margin-top: 4px; display: block;">Format: PDF, JPG, PNG, atau DOCX. Bukti otentik pencapaian.</span>
        </div>

        {{-- Evidence Notes --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Catatan Bukti Unggahan</label>
            <input type="text" name="evidence_notes" class="form-input" value="{{ old('evidence_notes') }}" placeholder="Contoh: Hasil kuesioner kepuasan Mei 2026">
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Simpan Pemantauan
            </button>
        </div>
    </form>
</div>
@endsection
