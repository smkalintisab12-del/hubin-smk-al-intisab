# Sistem Informasi Hubin SMK AL INTISAB

Aplikasi web Hubungan Industri (Hubin) berbasis PHP Native, MySQL, PDO, dan Bootstrap 5.

## Fitur
- Login admin dengan password hash dan session
- Dashboard ringkasan siswa, guru, industri, dan penempatan PKL
- CRUD data siswa, guru, dan industri
- Data penempatan PKL
- Monitoring PKL
- Penilaian PKL
- Laporan penempatan dan hasil penilaian (CSV)

## Persyaratan
- PHP 8.0 atau lebih baru
- MySQL 5.7+/MariaDB 10.4+
- Apache/Nginx (XAMPP/Laragon untuk lokal)
- Ekstensi PHP: PDO MySQL

## Instalasi lokal (XAMPP)
1. Ekstrak folder `hubin-smk-al-intisab` ke `C:/xampp/htdocs/`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka `http://localhost/phpmyadmin`.
4. Buat database `hubin_smkalintisab`, lalu impor `database/hubin.sql`.
5. Sesuaikan koneksi pada `app/config/database.php`.
6. Buka `http://localhost/hubin-smk-al-intisab/`.
7. Login awal:
   - Username: `admin`
   - Password: `password`
8. Segera ubah password awal melalui database atau tambahkan fitur penggantian password sebelum dipakai secara publik.

## Konfigurasi
Ubah konstanta `BASE_URL`, host, nama database, username, dan password di `app/config/database.php`.
Jangan unggah kredensial produksi ke repository publik. Gunakan environment variables atau file konfigurasi lokal yang dikecualikan dari Git.

## Struktur
```text
app/
  config/database.php
  helpers/auth.php
assets/css/app.css
database/hubin.sql
includes/header.php
includes/footer.php
index.php
login.php
logout.php
```

## Upload ke GitHub
1. Buat repository baru di GitHub, misalnya `hubin-smk-al-intisab`.
2. Buka terminal di folder project.
3. Jalankan:
   ```bash
   git init
   git add .
   git commit -m "Initial commit - Sistem Informasi Hubin"
   git branch -M main
   git remote add origin https://github.com/USERNAME/hubin-smk-al-intisab.git
   git push -u origin main
   ```
4. Ganti `USERNAME` dengan username GitHub Anda.

## Catatan keamanan
- Ganti password admin bawaan sebelum aplikasi dipublikasikan.
- Gunakan HTTPS pada server produksi.
- Atur backup database berkala.
- Batasi akses aplikasi dan lindungi data pribadi siswa.
- Jangan commit file `.env`, password, backup database berisi data asli, atau data pribadi.
- Aplikasi ini adalah fondasi MVP; lakukan pengujian, hardening, dan review hak akses sebelum digunakan untuk data sekolah sungguhan.

## Lisensi
Internal project template untuk pengembangan SMK AL INTISAB.
