@extends('layouts.iso')

@section('title', 'Tambah Program Kerja')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Tambah Program Kerja</h1>
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
    <form action="{{ route('quality-objectives.action-plans.store') }}" method="POST">
        @csrf
        <input type="hidden" name="objective_id" value="{{ $objective->id }}">

        {{-- Nama Program --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nama Program Kerja / Rencana Tindakan <span style="color: var(--error);">*</span></label>
            <input type="text" name="program_name" class="form-input @error('program_name') is-invalid @enderror" value="{{ old('program_name') }}" placeholder="Contoh: Mengadakan pelatihan internal, Upgrade RAM server" required>
            @error('program_name')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Urutan (Sequence) --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Urutan Langkah (Opsional)</label>
            <input type="number" name="sequence" class="form-input" value="{{ old('sequence') }}" placeholder="Contoh: 1, 2, 3 (kosongkan untuk auto)">
        </div>

        {{-- PIC --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">PIC Penanggung Jawab Program</label>
            <select name="pic_user_id" class="form-input">
                <option value="">-- Pilih PIC --</option>
                @foreach($users as $pic)
                    <option value="{{ $pic->id }}" {{ old('pic_user_id') == $pic->id ? 'selected' : '' }}>
                        {{ $pic->name }} ({{ $pic->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Target Date --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Target Tanggal Selesai <span style="color: var(--error);">*</span></label>
                <input type="date" name="target_date" class="form-input" value="{{ old('target_date') }}" required>
                @error('target_date')
                    <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Estimasi Anggaran --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Estimasi Anggaran (Rp)</label>
                <input type="number" name="budget_estimated" class="form-input" value="{{ old('budget_estimated', 0) }}" placeholder="Contoh: 5000000">
            </div>
        </div>

        {{-- Deskripsi Program --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi Rincian Tindakan</label>
            <textarea name="description" class="form-textarea" rows="3" placeholder="Tuliskan detail teknis pelaksanaan program kerja...">{{ old('description') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Simpan Program Kerja
            </button>
        </div>
    </form>
</div>
@endsection
