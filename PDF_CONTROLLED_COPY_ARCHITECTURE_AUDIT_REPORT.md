# PDF_CONTROLLED_COPY_ARCHITECTURE_AUDIT_REPORT

## 1. Jalur Akses PDF (PDF Access Paths Tracking)

Berdasarkan penelusuran kode rute dan pengontrol, berikut adalah semua rute yang mengalirkan file PDF ke pengguna:

| Route | Controller | Method | Inline Preview | Download | Logged? |
| ----- | ---------- | ------ | -------------- | -------- | ------- |
| `documents.versions.preview` | `DocumentController` | `previewVersion` | **YES** | NO | **YES** (`preview_pdf`) |
| `documents.versions.download` | `DocumentController` | `downloadVersion` | NO | **YES** | **YES** (`download_pdf`) |
| `documents.versions.downloadMaster` | `DocumentController` | `downloadMaster` | NO | **YES** | **YES** (`download_master`) |

*Kesimpulan:* Tidak ada rute bypass file statis statik langsung (direktori `storage/app/documents` diatur sebagai `visibility => private` tanpa symbolic link publik), sehingga 100% jalur akses PDF pasti melalui pengontrol Laravel dan tercatat log.

---

## 2. Klasifikasi Event Berdasarkan ISO 9001:2015 (Event Classification)

### Pertanyaan A: Klasifikasi `preview_pdf`
* **Pilihan:** **Access Event**
* **Penjelasan:** `preview_pdf` adalah aktivitas membaca dokumen secara digital di layar komputer melalui interface aplikasi. Berdasarkan prinsip ISO 9001:2015 Klausul 7.5.3 (Pengendalian Informasi Berdokumentasi), ini merupakan kontrol hak akses (*access control*), karena file fisik tidak berpindah ke media lokal pengguna secara terpisah.

### Pertanyaan B: Klasifikasi `download_pdf`
* **Pilihan:** **Distribution Event**
* **Penjelasan:** `download_pdf` memindahkan file PDF terkendali ke komputer lokal pengguna yang memungkinkannya dicetak secara fisik atau dibagikan ke pihak ketiga. Ini wajib diatur sebagai distribusi salinan terkendali (*controlled copies distribution*).

### Pertanyaan C: Klasifikasi `download_master`
* **Pilihan:** **Distribution Event**
* **Penjelasan:** `download_master` mengunduh berkas master (misalnya Word atau PDF master asli) ke penyimpanan lokal untuk keperluan revisi. Karena file keluar dari server utama, ini dikategorikan sebagai aktivitas distribusi.

---

## 3. Evaluasi Trace ID (Trace ID Audit)

### A. Keunikan Trace ID
* **Mekanisme:** Trace ID dibentuk dengan format `DISTR-YYYYMMDD-XXXXXX`.
  - `YYYYMMDD` diisi tanggal saat ini.
  - `XXXXXX` diisi nomor urut 6-digit ter-pad dengan nol (misal `000001`).
  - Dilindungi oleh loop pengecekan database `while(exists(...))` untuk memastikan tidak ada duplikasi data. Keunikan bernilai 100% aman.

### B. Keterlacakan (Searchability)
* **Jawab:** **YES**
* **Penjelasan:** Karena seluruh data snapshot disimpan di tabel `document_distribution_logs`, jika auditor membawa dokumen fisik bertuliskan `DISTR-20260623-000001`, admin dapat langsung mencarinya di menu **Distribution Register** untuk mengetahui nama user, email, departemen, versi dokumen yang diakses, IP address, dan tanggal distribusi secara instan.

---

## 4. Evaluasi Desain Stempel Controlled Copy (PDF Stamp Design Audit)

| Kriteria | OPTION A (Footer Horizontal) | OPTION B (Vertical Margin) | OPTION C (Footer Ringkas) |
| --- | --- | --- | --- |
| **Mudah dibaca auditor** | **Sangat Baik** (Membaca mendatar di bawah) | **Sedang** (Kertas harus diputar) | **Sangat Baik** (Hanya 1 kode pendek) |
| **Tidak mengganggu dokumen** | **Sangat Baik** (Di area bawah margin halaman) | **Baik** (Di margin samping) | **Sangat Baik** (Visual minimal) |
| **Mudah dicocokkan ke register** | **Sangat Baik** (Info detail tercetak langsung) | **Sedang** (Harus dicari di sistem) | **Sedang** (Hapus dicari di sistem) |
| **Kompleksitas implementasi** | **Rendah** (Koordinat teks lurus konstan) | **Sedang-Tinggi** (Perlu rotasi teks 90 derajat) | **Rendah** (String teks sangat pendek) |

### Rekomendasi Final Desain Stamp:
**OPTION A (Footer Horizontal)** direkomendasikan karena menyajikan informasi audit secara lengkap dan transparan langsung pada lembaran fisik tanpa mewajibkan auditor selalu mencocokkannya ke komputer untuk verifikasi lapangan.

---

## 5. Audit Alur Pengunduhan (Download UX Audit)

### Diagram Alur Aksi Pengguna:
```
[Aksi User] ───────────────> [Rute Laravel] ────────────> [Log Register Distribusi]
1. Tombol Download PDF  ───> documents.versions.download   ───> YES (download_pdf)
2. Tombol Download Master ─> documents.versions.downloadMaster ─> YES (download_master)
3. Link Open New Tab  ─────> documents.versions.preview    ───> YES (preview_pdf)
4. Tombol Save Browser  ───> Browser Cache (No Request)    ───> NO (Tercatat preview)
5. Tombol Print Browser ───> Browser Print (No Request)   ───> NO (Tercatat preview)
```

### Apakah saat ini ada duplikasi jalur distribusi?
* **YES**
* **Penjelasan:** 
  - Terdapat duplikasi logging jika user menekan tombol pratinjau (preview) lalu kemudian menekan tombol "Download PDF" resmi di detail dokumen. Hal ini mencatatkan dua entri log terpisah (`preview_pdf` dan `download_pdf`) untuk sesi baca dokumen yang sama.
  - Tombol simpan/cetak bawaan browser di penampil iframe juga dapat menyimpan dokumen tanpa memicu trigger logging `download_pdf` yang baru, melainkan hanya memanfaatkan data preview yang sudah di-cache browser.

---

## 6. Model Controlled Copy MVP (Controlled Copy Model MVP)

* **Model Pilihan:** **MODEL 3 (Semua masuk Distribution Register)**
* **Kesesuaian:**
  - **ISO 9001 & Audit TÜV:** Setiap tindakan mengalirkan dokumen terkendali (preview di layar, unduhan PDF, maupun file master) ke luar dari sistem basis data utama merupakan bentuk akses berkas terkendali yang wajib diawasi (Klausul 7.5.3).
  - **Operasional Pabrik:** Operator atau auditor sering kali melakukan "print screen" atau mencetak langsung dari preview. Jika preview tidak dicatat di log distribusi, maka salinan fisik liar dapat beredar tanpa Trace ID pelacak. Dengan MODEL 3, seluruh rute tersebut dijamin terstempel secara on-the-fly.

---

## 7. Keputusan Final Arsitektur (Final Verdict)

1. **Apakah Phase C2 siap dimulai?**
   - **YA.** Seluruh pondasi registrasi distribusi telah stabil dan siap untuk dipasangi mesin penstempel PDF.
2. **Apakah perlu perubahan arsitektur terlebih dahulu?**
   - **TIDAK.** Struktur tabel dan model saat ini sudah lengkap dan memadai.
3. **Desain stamp mana yang direkomendasikan?**
   - **OPTION A (Footer Horizontal)** yang berisi teks stempel audit lengkap di setiap halaman PDF.
4. **Jalur distribusi resmi mana yang harus digunakan sistem?**
   - Rute terpusat Laravel: `/documents/versions/{version}/preview` dan `/documents/versions/{version}/download`.
5. **Estimasi risiko implementasi FPDI:**
   - **LOW.** Integrasi perpustakaan FPDI di PHP bersifat terisolasi pada tingkat controller streaming dan tidak mempengaruhi alur kerja approval maupun versioning yang sudah berjalan stabil.
