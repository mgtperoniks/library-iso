# PDF_DISTRIBUTION_STAMP_AUDIT_REPORT

## 1. Jalur Akses PDF (PDF Access Paths)

Berdasarkan audit rute dan pengontrol aplikasi, berikut adalah seluruh jalur akses berkas PDF di dalam sistem Library-ISO:

| Route Name | URL | Controller | Method | Tujuan | File Source |
| --- | --- | --- | --- | --- | --- |
| `documents.versions.preview` | `/documents/versions/{version}/preview` | `DocumentController` | `previewVersion` | Menampilkan berkas PDF secara inline pada Viewer halaman detail dokumen. | `Storage::disk('documents')` -> `pdf_path`, `file_path`, atau `master_path` |
| `documents.versions.download` | `/documents/versions/{version}/download` | `DocumentController` | `downloadVersion` | Mengunduh PDF resmi dokumen. | `Storage::disk('documents')` -> `pdf_path`, `file_path`, atau `master_path` |
| `documents.versions.downloadMaster` | `/documents/versions/{version}/download-master` | `DocumentController` | `downloadMaster` | Mengunduh master file asli (Word/PDF). | `Storage::disk('documents')` -> `master_path`, `pdf_path`, atau `file_path` |

---

## 2. Alur Sumber Berkas Fisik (File Source Architecture)

Seluruh rute akses membaca file fisik yang sama dari media penyimpanan terproteksi (`Visibility: private` pada direktori `storage/app/documents`). Tidak ada akses publik langsung (symbolic link bypass) ke folder dokumen.

```
                ┌──────────────────────────────────┐
                │ Physical MASTER PDF              │
                │ (storage/app/documents/...)      │
                └────────────────┬─────────────────┘
                                 │
            ┌────────────────────┼────────────────────┐
            ▼                    ▼                    ▼
     [Preview PDF]        [Download PDF]       [Download Master]
     - via iframe src     - via Attachment     - via Attachment
     - inline streaming   - logged download    - logged master
```

---

## 3. Analisis Perilaku Pengunduhan Browser (Browser Save Behavior)

Ketika berkas PDF dimuat di dalam `iframe` menggunakan PDF Viewer bawaan browser (seperti Chrome PDF Viewer atau Edge PDF Reader):
1. Berkas PDF telah diunduh secara lokal ke dalam *memory buffer/cache* browser melalui rute `documents.versions.preview`.
2. Saat user menekan tombol **"Save"** (ikon disket) atau **"Print"** (ikon printer) bawaan browser di dalam toolbar `iframe`:
   - Browser menyimpan berkas langsung dari *memory cache* tanpa memicu request HTTP baru ke server Laravel.
   - **Risiko Bypass:** Tindakan klik tombol simpan bawaan browser ini tidak memicu rute `download` Laravel, sehingga tidak mencatat log aktivitas `download_pdf` yang terpisah di tabel log distribusi.
   - **Mitigasi:** Log akses `preview_pdf` tetap **pasti tercatat** sebelum berkas dapat ditampilkan di dalam `iframe`. Namun, untuk memastikan berkas yang disimpan dari browser tetap memiliki identitas penerima, stempel harus disisipkan langsung ke dalam PDF saat *preview stream* dikirim dari server.

---

## 4. Arsitektur Viewer PDF Saat Ini

Viewer PDF saat ini diimplementasikan menggunakan HTML `iframe` standar:
```html
<iframe id="pdfIframe" src="{{ $pdfUrl }}" width="100%" height="700" ...></iframe>
```
- **Kontrol:** Penampil berkas dikendalikan sepenuhnya secara **Native oleh Browser**. Aplikasi tidak memiliki kontrol atas render tombol unduh, cetak, atau seleksi teks di dalam penampil tersebut.
- **Konsekuensi terhadap Distribution Stamp:**
  - Kita tidak dapat menyisipkan elemen HTML/CSS di atas berkas PDF di dalam `iframe` karena keterbatasan keamanan lintas-dokumen dan sifat render berkas native.
  - Untuk menerapkan stempel distribusi (controlled copy watermarking), stempel **wajib disisipkan secara biner (burned-in)** langsung ke dalam berkas PDF di tingkat server sebelum dialirkan (*streamed*) ke browser.

---

## 5. Kelayakan & Desain Distribution Stamp (Feasibility)

Stempel Controlled Copy yang kompatibel dengan ISO 9001:2015 harus disisipkan pada setiap halaman berkas PDF.

### Pilihan Tata Letak (Layout Options)
- **Opsi A (Footer Horizontal - Direkomendasikan untuk MVP):** Menyisipkan 1 baris teks kecil (font size 7-8pt, warna abu-abu gelap atau merah bata) di bagian margin bawah (footer) setiap halaman.
  - *Format:* `DOKUMEN TERKENDALI - DISTR-YYYYMMDD-XXXXXX | [doc_code] Rev.[version] | Penerima: [email] | [datetime] | IP: [ip]`
- **Opsi B (Margin Vertikal):** Menyisipkan teks stempel secara vertikal pada margin kiri atau kanan halaman.
  - *Kekurangan:* Membutuhkan rotasi teks 90 derajat saat pembuatan stempel, serta sedikit mengganggu visual estetika pembacaan dokumen.
- **Opsi C (Footer + Trace ID Saja):** Hanya menyisipkan Trace ID saja di footer.
  - *Kekurangan:* Kurang informatif secara langsung bagi auditor fisik tanpa mencari di sistem terlebih dahulu.

**Rekomendasi Desain MVP:**
Menggunakan **Opsi A (Footer Horizontal)** di setiap halaman. Desain ini standar, mudah dibaca, serta memenuhi kepatuhan audit distribusi dokumen terkendali.

---

## 6. Perbandingan Model Implementasi (Performance Audit)

| Kriteria | Model A (On-the-fly Stamping) | Model B (Generate & Cache) | Model C (Client-side Overlay) |
| --- | --- | --- | --- |
| **Deskripsi** | Menghasilkan PDF berstempel di memori server setiap kali diminta, lalu dialirkan langsung. | Membuat PDF berstempel sekali per pengguna/versi, menyimpannya di cache, lalu menyajikannya dari cache. | Menyajikan PDF asli, lalu merender stempel sebagai lapisan HTML/JS di atas viewer. |
| **Kompleksitas** | **Medium** (Memerlukan library PHP PDF parser seperti `setasign/fpdi`). | **Tinggi** (Perlu manajemen siklus hidup berkas cache & penghapusan otomatis). | **Sangat Tinggi** (Perlu kustomisasi viewer menggunakan PDF.js). |
| **Risiko Keamanan** | **Sangat Rendah** (Stempel permanen terikat ke biner berkas). | **Sangat Rendah** (Stempel permanen terikat ke biner berkas). | **Tinggi** (Pengguna mahir dapat mengunduh PDF asli yang bersih lewat network tab). |
| **Beban Penyimpanan** | **Nol** (Tidak menggunakan ruang penyimpanan tambahan). | **Tinggi** (Penyimpanan membengkak karena setiap user memiliki berkas salinan terpisah). | **Nol** (PDF asli tetap satu). |
| **Kesesuaian Server** | **Sangat Cocok** (CPU i5 pada Geekom server sangat cepat memproses manipulasi PDF ringan). | **Kurang Cocok** (Pemborosan I/O disk). | **Sangat Cocok** (Beban dialihkan ke sisi klien). |

---

## 7. Model Pelacakan Salinan Terkendali (Controlled Copy Model)

Apabila auditor membawa lembar cetak fisik dokumen dengan stempel `DISTR-20260623-000001`:
- **Cara Pelacakan Saat Ini:**
  Petugas QMS/MR dapat masuk ke menu **Distribution Register** (`/distribution-register`) dan mengetikkan `DISTR-20260623-000001` pada kolom pencarian. Sistem akan menampilkan rincian:
  - **Penerima:** `mr@peroniks.com` (Nama & Email)
  - **Waktu Akses:** `2026-06-23 15:45:00`
  - **Alamat IP Pengakses:** `10.88.8.97`
  - **Versi Dokumen:** `v3`
- **Kondisi Kesiapan Sistem:**
  Struktur database `document_distribution_logs` saat ini **sudah 100% siap** mencatat seluruh informasi di atas. 
- **Perubahan Minimum yang Dibutuhkan:**
  Hanya menyisipkan proses *stamping* menggunakan pustaka PHP PDF (FPDI) saat user memicu fungsi `previewVersion` dan `downloadVersion` di `DocumentController.php` agar Trace ID tersebut tercetak secara fisik pada lembaran PDF.

---

## 8. Rekomendasi Akhir (Final Recommendation)

**Direkomendasikan: MODEL A (On-the-fly Stamping)**
- **Alasan Kepatuhan ISO 9001:2015:** Menjamin integritas salinan dokumen. File yang diunduh maupun dicetak secara langsung dari browser dipastikan membawa stempel distribusi permanen tanpa celah bypass.
- **Efisiensi:** Tanpa beban penyimpanan cache tambahan, bebas risiko kebocoran data, dan sangat ringan dijalankan di server Geekom Core i5 untuk kapasitas pengguna organisasi 100–200 orang.
