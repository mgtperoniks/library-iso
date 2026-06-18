@extends('layouts.iso')

@section('title', 'Tambah Sasaran Mutu')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Tambah Sasaran Mutu Baru</h1>
            <p class="sub">Step 1: Definisikan Sasaran Mutu (Objective Definition)</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.objectives.index') }}" class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>

<div class="card card-section card-inner" style="padding: 24px; max-width: 800px; margin: 0 auto;">
    <form action="{{ route('quality-objectives.objectives.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Periode --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Periode Tahun <span style="color: var(--error);">*</span></label>
                <select name="period_id" class="form-input" required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ old('period_id') == $period->id ? 'selected' : '' }}>
                            Tahun {{ $period->year }} ({{ $period->title }})
                        </option>
                    @endforeach
                </select>
                @error('period_id')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Departemen --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Departemen <span style="color: var(--error);">*</span></label>
                @if(auth()->user()->hasAnyRole(['kabag']))
                    <input type="text" class="form-input" value="{{ $departments->first()->name }}" style="background: #ededf9; cursor: not-allowed;" readonly>
                    <input type="hidden" name="department_id" value="{{ $departments->first()->id }}">
                @else
                    <select name="department_id" class="form-input" required>
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                [{{ $dept->code }}] {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('department_id')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Nama Proses --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nama Proses / Layanan <span style="color: var(--error);">*</span></label>
            <input type="text" name="process_name" class="form-input @error('process_name') is-invalid @enderror" value="{{ old('process_name') }}" placeholder="Contoh: Layanan Pengadaan Buku, Penanganan Komplain" required>
            @error('process_name')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Pernyataan Target / Sasaran --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Pernyataan Sasaran Mutu <span style="color: var(--error);">*</span></label>
            <textarea name="objective_statement" class="form-textarea" rows="3" placeholder="Tuliskan pernyataan sasaran mutu secara spesifik..." required>{{ old('objective_statement') }}</textarea>
            @error('objective_statement')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- KPI Indicator --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Indikator Kinerja Utama (KPI Indicator) <span style="color: var(--error);">*</span></label>
            <input type="text" name="kpi_indicator" class="form-input @error('kpi_indicator') is-invalid @enderror" value="{{ old('kpi_indicator') }}" placeholder="Contoh: Kecepatan pelayanan, Persentase error sistem" required>
            @error('kpi_indicator')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Target Value --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Target Nilai <span style="color: var(--error);">*</span></label>
                <input type="number" step="0.01" name="target_value" class="form-input @error('target_value') is-invalid @enderror" value="{{ old('target_value') }}" placeholder="Contoh: 95 atau 3" min="0" required>
                @error('target_value')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Satuan --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Satuan Target</label>
                <input type="text" name="unit" class="form-input @error('unit') is-invalid @enderror" value="{{ old('unit') }}" placeholder="Contoh: %, menit, keluhan">
                @error('unit')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Polaritas --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Polaritas Target <span style="color: var(--error);">*</span></label>
                <select name="target_polarity" class="form-input" required>
                    <option value="gte" {{ old('target_polarity') === 'gte' ? 'selected' : '' }}>&ge; (Makin tinggi makin baik)</option>
                    <option value="lte" {{ old('target_polarity') === 'lte' ? 'selected' : '' }}>&le; (Makin rendah makin baik)</option>
                </select>
                @error('target_polarity')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Frekuensi Pemantauan --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Frekuensi Pemantauan <span style="color: var(--error);">*</span></label>
                <select name="monitoring_frequency" class="form-input" required>
                    <option value="monthly" {{ old('monitoring_frequency') === 'monthly' ? 'selected' : '' }}>Bulanan (Monthly)</option>
                    <option value="quarterly" {{ old('monitoring_frequency') === 'quarterly' ? 'selected' : '' }}>Triwulan (Quarterly)</option>
                    <option value="biannual" {{ old('monitoring_frequency') === 'biannual' ? 'selected' : '' }}>Semester (Biannual)</option>
                    <option value="annual" {{ old('monitoring_frequency') === 'annual' ? 'selected' : '' }}>Tahunan (Annual)</option>
                </select>
                @error('monitoring_frequency')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- PIC --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">PIC Penanggung Jawab</label>
                <select name="pic_user_id" class="form-input">
                    <option value="">-- Pilih PIC --</option>
                    @foreach($users as $pic)
                        <option value="{{ $pic->id }}" {{ old('pic_user_id') == $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }} ({{ $pic->email }})
                        </option>
                    @endforeach
                </select>
                @error('pic_user_id')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Metode Pengukuran --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Metode Pengukuran / Cara Hitung</label>
            <textarea name="measurement_method" class="form-textarea" rows="2" placeholder="Contoh: (Jumlah keluhan terlayani / Total keluhan masuk) x 100%">{{ old('measurement_method') }}</textarea>
            @error('measurement_method')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Catatan / Notes --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Catatan Lainnya</label>
            <textarea name="notes" class="form-textarea" rows="2" placeholder="Tulis catatan tambahan terkait target sasaran mutu...">{{ old('notes') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Simpan Sasaran Mutu
            </button>
        </div>
    </form>
</div>
@endsection
