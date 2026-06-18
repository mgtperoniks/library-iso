@extends('layouts.iso')

@section('title', 'Edit Periode Sasaran Mutu ' . $period->year)

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Edit Periode: Tahun {{ $period->year }}</h1>
            <p class="sub">Perbarui informasi periode target dan status siklus sasaran mutu</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.periods.index') }}" class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>

<div class="card card-section card-inner" style="padding: 24px; max-width: 600px; margin: 0 auto;">
    <form action="{{ route('quality-objectives.periods.update', $period->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Tahun (readonly/disabled to protect identity, but we still pass it inside input) --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--muted);">Tahun Periode (Tidak dapat diubah)</label>
            <input type="number" name="year" class="form-input" value="{{ $period->year }}" style="background: #ededf9; cursor: not-allowed;" readonly>
        </div>

        {{-- Judul Periode --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Judul Periode <span style="color: var(--error);">*</span></label>
            <input type="text" name="title" class="form-input @error('title') is-invalid @enderror" value="{{ old('title', $period->title) }}" placeholder="Contoh: Rencana Sasaran Mutu 2026" required>
            @error('title')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Status Periode --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Status Periode <span style="color: var(--error);">*</span></label>
            <select name="status" class="form-input" style="padding: 10px;" required>
                <option value="draft" {{ old('status', $period->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="active" {{ old('status', $period->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="closed" {{ old('status', $period->status) === 'closed' ? 'selected' : '' }}>Closed (Read-Only Mode)</option>
                <option value="archived" {{ old('status', $period->status) === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            <span class="small-muted" style="margin-top: 6px; display: block;">
                <strong>Catatan:</strong> Mengubah status ke <strong>Closed</strong> akan mengunci seluruh data sasaran mutu pada periode tahun ini menjadi <em>read-only</em>.
            </span>
        </div>

        {{-- Deskripsi --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi / Catatan Tambahan</label>
            <textarea name="description" class="form-textarea" rows="4" placeholder="Tulis catatan atau kebijakan sasaran mutu tahun berjalan...">{{ old('description', $period->description) }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.periods.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Perbarui Periode
            </button>
        </div>
    </form>
</div>
@endsection
