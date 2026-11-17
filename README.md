# 📅 Sistem Generate & Manajemen Jadwal Pelajaran Sekolah

Aplikasi web berbasis **Laravel** dan **Vue 3 (TypeScript)** untuk mengelola data akademik sekolah serta menyusun jadwal pelajaran secara otomatis (**Auto-Generate Wizard**) maupun manual dengan deteksi bentrok (*conflict detection*), pengaturan JP (*Jam Pelajaran*), serta publikasi ke **Jadwal Resmi** yang terpisah dari *draft* penyusunan.

## ✨ Fitur Utama

### 1. Manajemen Data Master Akademik
* **Tahun Ajaran & Semester**: Pengelolaan periode akademik aktif (Ganjil/Genap).
* **Data Guru & Batas Beban JP**: Manajemen profil guru serta batas maksimal jam mengajar.
* **Mata Pelajaran & Kode Warna**: Pengaturan kode mapel dan warna identitas visual pada matriks jadwal.
* **Profil Slot Waktu (*Time Slot Profiles*)**: Mendukung multi-profil jam pelajaran (misalnya perbedaan jam pulang antar tingkat kelas/hari) beserta pengaturan jam istirahat atau kegiatan non-KBM.
* **Kelas & Plotting Guru Pengampu**: Pemetaan mata pelajaran per kelas lengkap dengan alokasi target JP dan guru pengampu (*team teaching* / paralel).

### 2. Wizard Auto-Generate & Editor Jadwal (Staging / Draft)
* **3 Mode Tampilan Matriks Interaktif**:
  * **Semua Kelas (*Master Matrix*)**: Pemantauan dan pengeditan jadwal seluruh kelas dalam satu layar.
  * **Per Kelas (*Class Matrix*)**: Fokus penyusunan per kelas dilengkapi **Sidebar Saldo Beban JP** secara *real-time*.
  * **Per Guru (*Teacher Matrix*)**: Pemantauan jadwal mengajar per guru termasuk deteksi kelas paralel/bersama.
* **Auto-Generate Jadwal**: Penyusunan otomatis per kelas maupun seluruh kelas sekaligus dengan mempertimbangkan ketersediaan guru, slot terkunci, dan kuota JP.
* **Gembok Slot (*Lock Slot*) & Kegiatan Khusus**: Mengunci jadwal tertentu (seperti Upacara, Ekstrakurikuler, atau jam khusus guru) agar tidak tertimpa saat proses *auto-generate* ulang.
* **Validasi Kuota JP Otomatis**: Mata pelajaran yang kuota JP-nya sudah 100% terplot otomatis dinonaktifkan/disembunyikan pada modal penugasan slot untuk mencegah kelebihan beban JP.
* **Isolasi Draft & Jadwal Resmi**: Perubahan, *reset*, atau *generate* ulang di dalam Wizard bekerja pada tabel *staging* (`schedules`) sehingga tidak merusak jadwal aktif sebelum admin menekan tombol **Publish Jadwal**.

### 3. Jadwal Resmi & Export Excel
* **Publikasi Terkontrol**: Konfirmasi timpa jadwal apabila sudah terdapat jadwal resmi aktif pada periode berjalan.
* **Tampilan Publik / Resmi**: Tampilan jadwal bersih (Semua Kelas & Per Kelas) untuk kebutuhan distribusi ke guru dan siswa.
* **Export & Import Excel**: Dukungan ekspor matriks jadwal dan data master ke format `.xlsx` / `.xls` menggunakan library spreadsheet (**Maatwebsite Laravel Excel / PhpSpreadsheet** pada backend serta **SheetJS (`xlsx`)** pada frontend).

## 🛠️ Teknologi & Library yang Digunakan

* **Backend**: Laravel (PHP 8.2+), Laravel Sanctum (API Authentication), Eloquent ORM
* **Frontend**: Vue 3 (`<script setup lang="ts">`), TypeScript, Pinia (State Management), Vue Router, Bootstrap 5 & Bootstrap Icons, Axios
* **Database**: MySQL / MariaDB
* **Library Excel**:
  * **Backend**: `maatwebsite/excel` (berbasis `phpoffice/phpspreadsheet`) untuk pemrosesan file Excel berskala besar di sisi server.
  * **Frontend**: `xlsx` (SheetJS) untuk generasi instan file spreadsheet langsung dari tampilan matriks jadwal.

## 🚀 Panduan Instalasi & Setup Project

### Prasyarat Sistem
Pastikan perangkat Anda sudah terpasang:
* **PHP** >= 8.2 & **Composer**
* **Node.js** >= 18.x & **NPM**
* **MySQL** / **MariaDB**

### Langkah 1: Clone Repository
```bash
git clone 
cd "Generate Jadwal"
```

### Langkah 2: Setup Backend (Laravel)
1. **Install dependensi PHP via Composer**:
   ```bash
   composer install
   ```

2. **Install library Excel (jika belum terpasang di `composer.json`)**:
   ```bash
   composer require maatwebsite/excel
   php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
   ```

3. **Salin file konfigurasi `.env` dan buat Application Key**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database di file `.env`**:
   Sesuaikan kredensial database lokal Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_generate_jadwal
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan Migrasi Database (dan Seeder jika tersedia)**:
   ```bash
   php artisan migrate
   # Opsional jika ada seeder data awal:
   # php artisan db:seed
   ```

6. **Buat Symbolic Link Storage (jika menggunakan upload/export file)**:
   ```bash
   php artisan storage:link
   ```

### Langkah 3: Setup Frontend (Vue 3 + TypeScript + Vite)
1. **Install dependensi Node.js**:
   ```bash
   npm install
   ```

2. **Install library Excel di Frontend (SheetJS) jika belum terpasang**:
   ```bash
   npm install xlsx
   ```

3. **Jalankan Vite Development Server**:
   ```bash
   npm run dev
   ```

### Langkah 4: Menjalankan Aplikasi
Buka dua terminal terpisah di direktori root project:

* **Terminal 1 (Server Backend Laravel)**:
  ```bash
  php artisan serve
  ```
* **Terminal 2 (Bundler Frontend Vite)**:
  ```bash
  npm run dev
  ```

Akses aplikasi melalui browser di alamat: **`http://127.0.0.1:8000`**
