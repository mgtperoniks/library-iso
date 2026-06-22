# UI AUDIT: CATEGORIES VS DEPARTMENTS

Dokumen ini berisi hasil audit visual dan struktural singkat untuk standardisasi tampilan halaman Categories mengikuti desain halaman Departments sebagai referensi utama.

---

## LANGKAH 1: Identifikasi File

### Departments
* **Route**: `/departments` (name: `departments.index`)
* **Controller**: `App\Http\Controllers\DepartmentController@index`
* **View**: `resources/views/departments/index.blade.php`

### Categories
* **Route**: `/categories` (name: `categories.index`)
* **Controller**: `App\Http\Controllers\CategoryController@index`
* **View**: `resources/views/categories/index.blade.php`

---

## LANGKAH 2: Tabel Perbandingan Komponen

| Komponen | Departments (Standar) | Categories (Kondisi Sekarang) | Berbeda? |
| :--- | :--- | :--- | :--- |
| **Layout Base** | `@extends('layouts.iso')` | `@extends('layouts.iso')` | **Tidak** |
| **Topbar Title** | "Daftar Departemen" (dikirim via `@section('title')`) | "ISO Library" (fallback default, tidak mengirim title) | **Ya** |
| **Header Luar Card** | Tidak ada (Judul langsung di dalam card) | Ada (`.site-header` berisi judul, deskripsi, & tombol tambah) | **Ya** |
| **Struktur Card** | Inline style (`border-radius:16px`, shadow custom, padding 24px) | Class style (`.page-card` & nested `.card-section .card-inner`) | **Ya** |
| **Tabel Shell** | Inline style (`width:100%`, `min-width:860px`) | Class style (`.table`) | **Ya** |
| **Header Tabel (TH)**| Title Case, tanpa background, border bawah tipis | UPPERCASE, background abu (`#f3f3fe`), border abu gelap | **Ya** |
| **Badge Aktif** | Biru pastel transparan (`#e8f0ff` text `#2563eb`) | Hijau solid (`.badge-success`) | **Ya** |
| **Badge Pending** | Kuning pastel transparan (`#fff4cc` text `#8a6d00`) jika > 0, Abu-abu jika 0 | Oranye solid (`.badge-warning`) | **Ya** |
| **Tombol Aksi** | `font-weight: 600`, link langsung ke detail route | `font-weight: 500`, link filter pencarian dokumen | **Ya** |

---

## LANGKAH 3: Mengapa Tampilan Categories Berbeda Jauh dari Departments?

1. **Inkonsistensi Penggunaan CSS**: Halaman Departments menggunakan *inline styles* (`style="..."`) langsung pada elemen HTML, sedangkan Categories menggunakan class utilitas terpusat dari berkas `public/css/style.css`.
2. **Arsitektur Card yang Berbeda**: Categories menggunakan struktur pembungkus ganda (`.page-card` dan `.card-inner`), sedangkan Departments menggunakan kontainer div tunggal yang di-style manual.
3. **Pengaturan Judul Halaman**: Halaman Categories tidak mendefinisikan `@section('title')`, sehingga Topbar Title gagal di-update dan menampilkan teks bawaan "ISO Library".
4. **Header Section**: Categories memiliki area header terpisah di luar card untuk menampung judul, deskripsi, dan tombol tambah dokumen, sedangkan Departments meletakkan judul langsung di dalam card.
5. **Skema Warna Badge**: Badge pada Categories menggunakan warna solid dengan kontras tinggi (hijau dan oranye), sedangkan Departments menggunakan skema warna pastel transparan (soft blue dan soft gold).
6. **Logika Badge In-Progress**: Badge in-progress pada Departments berupa tautan/link dinamis ke approval queue (jika jumlah > 0) atau badge abu statis (jika 0), sedangkan Categories selalu berupa badge oranye statis.
7. **Gaya Penulisan Header Tabel**: Header tabel Categories dipaksa menjadi UPPERCASE dan memiliki warna latar belakang (background fill), sedangkan Departments menggunakan Title Case tanpa background fill.
8. **Spasi dan Padding**: Ukuran padding card (`24px` pada Departments vs `14px` pada Categories) dan tinggi sel tabel berbeda karena perbedaan inline style vs class system.
9. **Tujuan Tautan Tombol Aksi**: Tombol "Buka Dokumen" pada Departments mengarah ke halaman detail masing-masing departemen (`departments.show`), sedangkan Categories mengarah ke halaman daftar dokumen yang difilter dengan kata kunci pencarian.

---

## LANGKAH 4: Rekomendasi Standardisasi

### KEEP
* Pengambilan data perhitungan relasi jumlah dokumen (`active_count` dan `in_progress_count`) di `CategoryController`.
* Pewarisan template `@extends('layouts.iso')`.

### CHANGE
* Tambahkan `@section('title', 'Daftar Kategori')` pada `categories/index.blade.php` agar judul topbar sinkron.
* Ubah pembungkus luar tabel Categories dari class `.page-card` & `.card-inner` menjadi struktur inline style persis seperti Departments:
  `<div style="max-width:1200px;margin:28px auto;padding:0 16px;box-sizing:border-box;">`
    `<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(20,40,80,0.04);">`
* Ubah header tabel Categories menjadi Title Case tanpa background fill, serta sesuaikan padding sel menjadi `12px 10px` dengan border bawah `#f4f6fa`.
* Ubah tampilan badge pada Categories menjadi warna pastel transparan sesuai standar Departments.
* Ubah tombol aksi "Buka Dokumen" untuk menggunakan `font-weight: 600`.

### REMOVE
* Hapus elemen `.site-header` yang berisi judul luar, sub-judul, dan tombol `+ New Document` di halaman Categories index untuk menyamakan kerapian tata letak Departments.
* Hapus class `.page-card`, `.card-section`, dan `.card-inner` dari file view Categories index.
