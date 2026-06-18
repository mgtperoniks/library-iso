@extends('layouts.iso')

@section('title', 'Edit Data Pemantauan')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Edit Realisasi Pemantauan</h1>
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
    <form action="{{ route('quality-objectives.monitorings.update', $monitoring->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="objective_id" value="{{ $objective->id }}">
        <input type="hidden" name="period_year" value="{{ $monitoring->period_year }}">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Period Label --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Label Periode Lapor <span style="color: var(--error);">*</span></label>
                <input type="text" name="period_label" class="form-input @error('period_label') is-invalid @enderror" value="{{ old('period_label', $monitoring->period_label) }}" required>
                @error('period_label')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Target Snapshot --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Target Snapshot <span style="color: var(--error);">*</span></label>
                <input type="number" step="0.01" name="target_snapshot" class="form-input" value="{{ old('target_snapshot', $monitoring->target_snapshot) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Bulan (Month) --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Bulan Ke (1-12)</label>
                <input type="number" name="period_month" class="form-input" value="{{ old('period_month', $monitoring->period_month) }}" min="1" max="12">
            </div>

            {{-- Kuartal (Quarter) --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Kuartal Ke (1-4)</label>
                <input type="number" name="period_quarter" class="form-input" value="{{ old('period_quarter', $monitoring->period_quarter) }}" min="1" max="4">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Realization Value --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nilai Realisasi <span style="color: var(--error);">*</span></label>
                <input type="number" step="0.01" name="realization_value" class="form-input @error('realization_value') is-invalid @enderror" value="{{ old('realization_value', $monitoring->realization_value) }}" required min="0">
                @error('realization_value')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Sumber Data --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Sumber Data / Dokumen Acuan</label>
                <input type="text" name="data_source" class="form-input" value="{{ old('data_source', $monitoring->data_source) }}">
            </div>
        </div>

        {{-- Notes / Analysis --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Analisis & Catatan Hambatan</label>
            <textarea name="notes" class="form-textarea" rows="3" placeholder="Tuliskan analisis mengapa target tercapai / tidak tercapai...">{{ old('notes', $monitoring->notes) }}</textarea>
        </div>

        {{-- Existing Evidence --}}
        @if($monitoring->evidences->count() > 0)
            <div style="background: var(--surface-container-low); padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                <strong>Bukti Unggahan Saat Ini:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                    @foreach($monitoring->evidences as $evidence)
                        <li>
                            <a href="{{ asset($evidence->file_path) }}" target="_blank">Lihat File Bukti</a> 
                            ({{ $evidence->notes }})
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Evidence File --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Unggah Bukti Baru (Akan menggantikan bukti lama)</label>
            <input type="file" name="evidence_file" class="form-input" style="padding: 8px;">
        </div>

        {{-- Evidence Notes --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Catatan Bukti Baru</label>
            <input type="text" name="evidence_notes" class="form-input" placeholder="Contoh: Laporan revisi pencapaian Mei">
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Perbarui Pemantauan
            </button>
        </div>
    </form>
</div>
@endsection
