@extends('layouts.iso')

@section('title', 'Distribution Register')

@section('content')
<style>
  /* Clean pagination styling */
  @media (min-width: 640px) {
      .pagination-wrapper nav > div:first-child {
          display: none !important;
      }
  }
  @media (max-width: 639px) {
      .pagination-wrapper nav > div:last-child {
          display: none !important;
      }
  }
  .pagination-wrapper nav p {
      display: none !important;
  }
  .pagination-wrapper nav > div:last-child {
      display: flex !important;
      justify-content: flex-end !important;
  }

  /* Modernized pagination links */
  .pagination-wrapper nav span.relative.z-0 {
      display: inline-flex !important;
      gap: 8px !important;
      box-shadow: none !important;
      background: transparent !important;
      border: none !important;
  }
  .pagination-wrapper nav span.relative.z-0 a,
  .pagination-wrapper nav span.relative.z-0 span[aria-disabled="true"] > span,
  .pagination-wrapper nav span.relative.z-0 span.cursor-default {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 36px !important;
      height: 36px !important;
      padding: 0 !important;
      font-size: 0.85rem !important;
      font-weight: 600 !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 50% !important;
      background-color: #f8fafc !important;
      color: #64748b !important;
      text-decoration: none !important;
      transition: all 0.15s ease-in-out !important;
      box-sizing: border-box !important;
  }
  .pagination-wrapper nav span.relative.z-0 span[aria-current="page"] > span {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 36px !important;
      height: 36px !important;
      padding: 0 !important;
      font-size: 0.85rem !important;
      font-weight: 700 !important;
      border: 1px solid #1d4ed8 !important;
      border-radius: 50% !important;
      background-color: #1d4ed8 !important;
      color: #ffffff !important;
      box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2) !important;
      box-sizing: border-box !important;
  }
  .pagination-wrapper nav span.relative.z-0 a:hover {
      border-color: #cbd5e1 !important;
      background-color: #e2e8f0 !important;
      color: #0f172a !important;
      transform: translateY(-1px);
  }
  .pagination-wrapper nav span.relative.z-0 span[aria-disabled="true"] > span:not(:has(svg)) {
      border: none !important;
      background: transparent !important;
      color: #94a3b8 !important;
      cursor: default !important;
  }

  /* Form & input elements styling */
  .input-modern {
      width: 100%;
      height: 42px;
      padding: 0 14px;
      border: 1px solid #cbd5e1 !important;
      border-radius: 8px !important;
      font-size: 0.9rem;
      box-sizing: border-box;
      outline: none;
      background-color: #ffffff;
      transition: all 0.15s ease-in-out !important;
  }
  .input-modern:hover {
      border-color: #94a3b8 !important;
  }
  .input-modern:focus {
      border-color: #1d4ed8 !important;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
  }

  .select-modern {
      width: 100%;
      height: 42px;
      padding: 0 40px 0 14px !important;
      border: 1px solid #cbd5e1 !important;
      border-radius: 8px !important;
      font-size: 0.9rem;
      box-sizing: border-box;
      outline: none;
      color: #0f172a;
      background-color: #ffffff;
      cursor: pointer;
      appearance: none !important;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
      background-repeat: no-repeat !important;
      background-position: right 14px center !important;
      background-size: 16px !important;
      transition: all 0.15s ease-in-out !important;
  }
  .select-modern:hover {
      border-color: #94a3b8 !important;
      background-color: #f8fafc;
  }
  .select-modern:focus {
      border-color: #1d4ed8 !important;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
      background-color: #ffffff;
  }

  .badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1.2;
  }
  .badge-preview {
      background-color: #e0f2fe;
      color: #0369a1;
  }
  .badge-download-pdf {
      background-color: #e0e7ff;
      color: #4338ca;
  }
  .badge-download-master {
      background-color: #d1fae5;
      color: #047857;
  }
</style>

<div style="max-width:1200px;margin:28px auto;padding:0 16px;box-sizing:border-box;">
  
  {{-- Header section --}}
  <div style="margin-bottom: 24px;">
    <h1 style="margin:0 0 4px 0; font-size:1.6rem; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
      <span class="material-symbols-outlined" style="font-size:32px; color:#1d4ed8;">assignment_ind</span>
      Distribution Register
    </h1>
    <p style="margin:0; font-size:0.9rem; color:#64748b;">
      Riwayat distribusi dokumen digital dan aktivitas akses pengguna.
    </p>
  </div>

  <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(20,40,80,0.04); border:1px solid #f1f5f9;">

    {{-- Filter Form Grid --}}
    <form method="get" action="{{ route('distribution.index') }}" style="margin-bottom:24px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:16px;">
        
        {{-- Start Date --}}
        <div>
          <label style="display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:6px;">Tanggal Awal</label>
          <input
            type="date"
            name="start_date"
            value="{{ request('start_date') }}"
            class="input-modern"
          >
        </div>

        {{-- End Date --}}
        <div>
          <label style="display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:6px;">Tanggal Akhir</label>
          <input
            type="date"
            name="end_date"
            value="{{ request('end_date') }}"
            class="input-modern"
          >
        </div>

        {{-- Document Search --}}
        <div>
          <label style="display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:6px;">Dokumen</label>
          <input
            type="text"
            name="document"
            placeholder="Cari Kode / Judul..."
            value="{{ request('document') }}"
            class="input-modern"
          >
        </div>

        {{-- User Search --}}
        <div>
          <label style="display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:6px;">User</label>
          <input
            type="text"
            name="user"
            placeholder="Cari Nama / Email..."
            value="{{ request('user') }}"
            class="input-modern"
          >
        </div>

        {{-- Activity Dropdown --}}
        <div>
          <label style="display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:6px;">Aktivitas</label>
          <select name="action" class="select-modern">
            <option value="">-- Semua Aktivitas --</option>
            <option value="preview_pdf" {{ request('action') === 'preview_pdf' ? 'selected' : '' }}>Membuka PDF</option>
            <option value="download_pdf" {{ request('action') === 'download_pdf' ? 'selected' : '' }}>Mengunduh PDF</option>
            <option value="download_master" {{ request('action') === 'download_master' ? 'selected' : '' }}>Mengunduh File Master</option>
          </select>
        </div>

      </div>

      {{-- Action Buttons --}}
      <div style="display:flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap:12px; border-top:1px solid #e2e8f0; padding-top:16px;">
        <div style="display:flex; gap:8px;">
          <button type="submit" style="height:40px;padding:0 20px;background:#1d4ed8;color:#ffffff;border:1px solid #1d4ed8;border-radius:8px;font-weight:600;font-size:0.88rem;cursor:pointer;transition:0.15s;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
            <span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span> Terapkan Filter
          </button>

          <a href="{{ route('distribution.index') }}" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 20px;background:#ffffff;color:#64748b;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;font-size:0.88rem;text-decoration:none;transition:0.15s;box-sizing:border-box;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
            <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Reset
          </a>
        </div>

        <div>
          <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 20px;background:#10b981;color:#ffffff;border:1px solid #10b981;border-radius:8px;font-weight:600;font-size:0.88rem;text-decoration:none;transition:0.15s;box-sizing:border-box;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
            <span class="material-symbols-outlined" style="font-size:18px;">download</span> Export CSV
          </a>
        </div>
      </div>
    </form>

    {{-- Data Table --}}
    <div style="overflow:auto; border:1px solid #f1f5f9; border-radius:12px;">
      <table style="width:100%;border-collapse:collapse;min-width:1000px; font-size:0.88rem;">
        <thead>
          <tr style="text-align:left;border-bottom:2px solid #edf2f7;background:#f8fafc;">
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:150px;">Waktu</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:180px;">Aktivitas</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;">Dokumen</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:90px;">Versi</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:220px;">User</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:120px;">Departemen</th>
            <th style="padding:14px 16px;font-weight:600;color:#475569;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em; width:130px;">IP Address</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr style="border-bottom:1px solid #f1f5f9; transition: background 0.1s;" onmouseover="this.style.background='#fafafb'" onmouseout="this.style.background='transparent'">
              
              {{-- Waktu --}}
              <td style="padding:14px 16px; color:#334155; font-weight:500;">
                {{ $log->created_at->format('d-M-Y H:i') }}
              </td>

              {{-- Aktivitas --}}
              <td style="padding:14px 16px;">
                @if($log->action === 'preview_pdf')
                  <span class="badge badge-preview">Membuka PDF</span>
                @elseif($log->action === 'download_pdf')
                  <span class="badge badge-download-pdf">Mengunduh PDF</span>
                @elseif($log->action === 'download_master')
                  <span class="badge badge-download-master">Mengunduh File Master</span>
                @else
                  <span class="badge" style="background:#e2e8f0; color:#475569;">{{ $log->action }}</span>
                @endif
                <div style="font-size:0.68rem; color:#94a3b8; font-family:monospace; margin-top:3px;">
                  {{ $log->trace_id }}
                </div>
              </td>

              {{-- Dokumen --}}
              <td style="padding:14px 16px;">
                <div style="font-weight:700; color:#0f172a; font-size:0.85rem;">
                  {{ $log->doc_code ?? '-' }}
                </div>
                <div style="color:#64748b; font-size:0.78rem; margin-top:2px;">
                  {{ $log->document_title ?? '-' }}
                </div>
              </td>

              {{-- Versi --}}
              <td style="padding:14px 16px; color:#475569; font-weight:600;">
                {{ $log->version_label ?? '-' }}
              </td>

              {{-- User --}}
              <td style="padding:14px 16px;">
                <div style="font-weight:600; color:#334155;">
                  {{ $log->user_email }}
                </div>
                <div style="color:#94a3b8; font-size:0.75rem; margin-top:2px; display:inline-flex; align-items:center; gap:4px;">
                  <span>{{ $log->user_name }}</span>
                  <span style="color:#cbd5e1;">•</span>
                  <span style="text-transform: capitalize; color:#64748b;">{{ $log->user_role }}</span>
                </div>
              </td>

              {{-- Departemen --}}
              <td style="padding:14px 16px; color:#475569;">
                {{ $log->user_department ?? '-' }}
              </td>

              {{-- IP Address --}}
              <td style="padding:14px 16px; font-family:monospace; color:#64748b; font-size:0.8rem;">
                {{ $log->ip_address ?? '-' }}
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="7" style="padding:40px 16px; text-align:center; color:#94a3b8;">
                <span class="material-symbols-outlined" style="font-size:48px; color:#cbd5e1; display:block; margin-bottom:8px;">assignment_late</span>
                Belum ada data distribusi tercatat untuk filter ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
      <div class="pagination-wrapper" style="margin-top:24px;">
        {!! $logs->links() !!}
      </div>
    @endif

  </div>
</div>
@endsection
