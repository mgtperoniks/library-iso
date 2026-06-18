@extends('layouts.iso')

@section('title', 'Edit Program Kerja')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Edit Program Kerja</h1>
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
    <form action="{{ route('quality-objectives.action-plans.update', $actionPlan->id) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="objective_id" value="{{ $objective->id }}">

        {{-- Nama Program --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nama Program Kerja / Rencana Tindakan <span style="color: var(--error);">*</span></label>
            <input type="text" name="program_name" class="form-input @error('program_name') is-invalid @enderror" value="{{ old('program_name', $actionPlan->program_name) }}" required>
            @error('program_name')
                <span style="color: var(--error); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Urutan (Sequence) --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Urutan</label>
                <input type="number" name="sequence" class="form-input" value="{{ old('sequence', $actionPlan->sequence) }}">
            </div>

            {{-- PIC --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">PIC Program</label>
                <select name="pic_user_id" class="form-input">
                    <option value="">-- Pilih PIC --</option>
                    @foreach($users as $pic)
                        <option value="{{ $pic->id }}" {{ old('pic_user_id', $actionPlan->pic_user_id) == $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Target Date --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Target Tanggal Selesai <span style="color: var(--error);">*</span></label>
                <input type="date" name="target_date" class="form-input" value="{{ old('target_date', $actionPlan->target_date ? $actionPlan->target_date->format('Y-m-d') : '') }}" required>
            </div>

            {{-- Estimasi Anggaran --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Estimasi Anggaran (Rp)</label>
                <input type="number" name="budget_estimated" class="form-input" value="{{ old('budget_estimated', $actionPlan->budget_estimated) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            {{-- Progress % --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Kemajuan (Progress %)</label>
                <input type="number" name="progress_pct" class="form-input" value="{{ old('progress_pct', $actionPlan->progress_pct ?? 0) }}" min="0" max="100">
            </div>

            {{-- Status --}}
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Status Program <span style="color: var(--error);">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="open" {{ old('status', $actionPlan->status) === 'open' ? 'selected' : '' }}>Open (Belum Mulai)</option>
                    <option value="in_progress" {{ old('status', $actionPlan->status) === 'in_progress' ? 'selected' : '' }}>In Progress (Sedang Jalan)</option>
                    <option value="completed" {{ old('status', $actionPlan->status) === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="overdue" {{ old('status', $actionPlan->status) === 'overdue' ? 'selected' : '' }}>Overdue (Terlambat)</option>
                    <option value="cancelled" {{ old('status', $actionPlan->status) === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                </select>
            </div>
        </div>

        {{-- Actual Completion Date --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Tanggal Selesai Aktual (Actual Date)</label>
            <input type="date" name="actual_date" class="form-input" value="{{ old('actual_date', $actionPlan->actual_date ? $actionPlan->actual_date->format('Y-m-d') : '') }}">
        </div>

        {{-- Deskripsi Program --}}
        <div class="form-row" style="margin-bottom: 16px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi Rincian Tindakan</label>
            <textarea name="description" class="form-textarea" rows="2">{{ old('description', $actionPlan->description) }}</textarea>
        </div>

        {{-- Catatan Penyelesaian --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Catatan Hasil Penyelesaian</label>
            <textarea name="completion_notes" class="form-textarea" rows="2" placeholder="Tuliskan evaluasi atau hambatan saat pelaksanaan program...">{{ old('completion_notes', $actionPlan->completion_notes) }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined">save</span>
                Perbarui Program Kerja
            </button>
        </div>
    </form>
</div>
@endsection
