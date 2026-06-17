@extends('layouts.iso')

@section('title', 'Review Program — ISO Library')

@section('content')
<div style="max-width:1200px; margin: 18px auto;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
    <div>
      <h2 style="margin:0;">Review Program</h2>
      <p style="margin:4px 0 0 0; color:#6b7280; font-size:.95rem;">Daftar dokumen yang melewati batas waktu tinjauan berkala (ISO 9001:2015 Clause 7.5.3.2).</p>
    </div>
  </div>

  @if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:12px; border-radius:8px; margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:16px;">
      {{ session('error') }}
    </div>
  @endif

  <div style="background:#fff; border:1px solid #eef3f8; border-radius:12px; padding:20px; box-shadow:0 4px 6px rgba(0,0,0,0.02);">
    @if($documents->isEmpty())
      <div style="text-align:center; padding:40px 20px; color:#6b7280;">
        <span style="font-size:2rem; display:block; margin-bottom:10px;">✅</span>
        Tidak ada dokumen yang jatuh tempo untuk ditinjau saat ini.
      </div>
    @else
      <table class="table" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:2px solid #f1f5f9; text-align:left;">
            <th style="padding:12px 8px; font-weight:600; color:#374151;">Kode Dokumen</th>
            <th style="padding:12px 8px; font-weight:600; color:#374151;">Judul</th>
            <th style="padding:12px 8px; font-weight:600; color:#374151;">Departemen</th>
            <th style="padding:12px 8px; font-weight:600; color:#374151;">Versi Aktif</th>
            <th style="padding:12px 8px; font-weight:600; color:#374151;">Next Review</th>
            <th style="padding:12px 8px; font-weight:600; color:#374151; text-align:right;">Tindakan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($documents as $doc)
            <tr style="border-bottom:1px solid #f1f5f9;">
              <td style="padding:14px 8px; font-weight:500; color:#111827;">
                <a href="{{ route('documents.show', $doc->id) }}" style="color:#0b5ed7; text-decoration:none;">{{ $doc->doc_code }}</a>
              </td>
              <td style="padding:14px 8px; color:#4b5563;">{{ $doc->title }}</td>
              <td style="padding:14px 8px; color:#4b5563;">{{ $doc->department->code ?? '-' }}</td>
              <td style="padding:14px 8px;">
                <span style="background:#eef2f6; color:#475569; padding:4px 8px; border-radius:6px; font-size:.85rem; font-weight:500;">
                  {{ $doc->currentVersion->version_label ?? '-' }}
                </span>
              </td>
              <td style="padding:14px 8px; color:#ef4444; font-weight:500;">
                {{ $doc->next_review_date ? $doc->next_review_date->format('Y-m-d') : 'Belum Dijadwalkan' }}
              </td>
              <td style="padding:14px 8px; text-align:right;">
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                  <!-- Still Relevant Button -->
                  <button type="button" 
                          class="btn" 
                          style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-size:.9rem;"
                          onclick="openReviewModal({{ $doc->id }}, '{{ e($doc->doc_code) }}', 'still-relevant')">
                    Still Relevant
                  </button>

                  <!-- Needs Revision Button -->
                  <button type="button" 
                          class="btn" 
                          style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:.9rem;"
                          onclick="openReviewModal({{ $doc->id }}, '{{ e($doc->doc_code) }}', 'needs-revision')">
                    Needs Revision
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      @if(method_exists($documents, 'links'))
        <div style="margin-top:20px;">
          {{ $documents->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

<!-- Review Confirmation Modal -->
<div id="reviewModal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); width:520px; max-width:95%; z-index:9999; background:#fff; padding:24px; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.2); border:1px solid #e2e8f0;">
  <h3 id="modalTitle" style="margin-top:0; margin-bottom:8px; color:#111827;">Tinjau Dokumen</h3>
  <p id="modalSubtitle" style="margin-top:0; margin-bottom:16px; color:#6b7280; font-size:.9rem;"></p>

  <form id="modalForm" method="POST" action="">
    @csrf
    <label for="review_note" style="font-weight:600; font-size:.9rem; color:#374151; display:block; margin-bottom:6px;">Catatan Tinjauan (Opsional)</label>
    <textarea id="review_note" name="note" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; box-sizing:border-box; resize:vertical;" placeholder="Tulis catatan atau temuan audit di sini..."></textarea>

    <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
      <button type="button" class="btn" style="background:#f3f4f6; border:1px solid #e5e7eb; color:#4b5563;" onclick="closeReviewModal()">Cancel</button>
      <button type="submit" id="submitBtn" class="btn" style="color:#fff; border:none;"></button>
    </div>
  </form>
</div>

<!-- Modal Backdrop -->
<div id="modalBackdrop" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9998;" onclick="closeReviewModal()"></div>

<script>
function openReviewModal(docId, docCode, actionType) {
  var modal = document.getElementById('reviewModal');
  var backdrop = document.getElementById('modalBackdrop');
  var form = document.getElementById('modalForm');
  var title = document.getElementById('modalTitle');
  var subtitle = document.getElementById('modalSubtitle');
  var submitBtn = document.getElementById('submitBtn');

  // reset note field
  document.getElementById('review_note').value = '';

  if (actionType === 'still-relevant') {
    title.textContent = 'Konfirmasi Dokumen Masih Relevan';
    subtitle.textContent = 'Tindakan ini menyatakan bahwa dokumen ' + docCode + ' masih sesuai kebutuhan operasional dan tidak memerlukan perubahan.';
    submitBtn.textContent = 'Konfirmasi & Jadwalkan Ulang';
    submitBtn.style.background = '#16a34a';
    form.action = '{{ url("/reviews") }}/' + docId + '/still-relevant';
  } else {
    title.textContent = 'Konfirmasi Perlu Revisi';
    subtitle.textContent = 'Tindakan ini menyatakan bahwa dokumen ' + docCode + ' memerlukan revisi. Anda akan diarahkan ke halaman pembuatan versi baru.';
    submitBtn.textContent = 'Buat Revisi Baru';
    submitBtn.style.background = '#dc2626';
    form.action = '{{ url("/reviews") }}/' + docId + '/needs-revision';
  }

  modal.style.display = 'block';
  backdrop.style.display = 'block';
}

function closeReviewModal() {
  document.getElementById('reviewModal').style.display = 'none';
  document.getElementById('modalBackdrop').style.display = 'none';
}
</script>
@endsection
