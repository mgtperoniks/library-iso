@extends('layouts.iso')

@section('title', 'Tambah Periode Sasaran Mutu')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Tambah Periode Sasaran Mutu</h1>
            <p class="sub">Tentukan tahun target baru untuk sistem sasaran mutu</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.periods.index') }}" class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>

<div class="card card-section card-inner" style="padding: 24px; max-width: 600px; margin: 0 auto;">
    <form action="{{ route('quality-objectives.periods.store') }}" method="POST">
        @csrf

        {{-- Tahun --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Tahun Periode <span style="color: var(--error);">*</span></label>
            <input type="number" name="year" class="form-input @error('year') is-invalid @enderror" value="{{ old('year', date('Y')) }}" placeholder="Contoh: 2026" min="2020" max="2100" required>
            @error('year')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Judul Periode --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Judul Periode <span style="color: var(--error);">*</span></label>
            <input type="text" name="title" class="form-input @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Rencana Sasaran Mutu 2026" required>
            @error('title')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Deskripsi --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi / Catatan Tambahan</label>
            <textarea name="description" class="form-textarea" rows="4" placeholder="Tulis catatan atau kebijakan sasaran mutu tahun berjalan...">{{ old('description') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.periods.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Simpan Periode
            </button>
        </div>
    </form>
</div>
@endsection
