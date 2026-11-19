CREATE DATABASE IF NOT EXISTS hubin_smkalintisab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hubin_smkalintisab;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  kelas VARCHAR(30) NOT NULL,
  jurusan VARCHAR(100) NOT NULL,
  no_hp VARCHAR(30) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS guru (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nip VARCHAR(40) DEFAULT '',
  nama VARCHAR(120) NOT NULL,
  jabatan VARCHAR(100) DEFAULT '',
  no_hp VARCHAR(30) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS industri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_perusahaan VARCHAR(150) NOT NULL,
  bidang VARCHAR(120) DEFAULT '',
  alamat TEXT,
  nama_pic VARCHAR(120) DEFAULT '',
  no_hp VARCHAR(30) DEFAULT '',
  email VARCHAR(120) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pkl (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  industri_id INT NOT NULL,
  guru_id INT NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  status ENUM('Berjalan','Selesai','Dibatalkan') DEFAULT 'Berjalan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pkl_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pkl_industri FOREIGN KEY (industri_id) REFERENCES industri(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pkl_guru FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS monitoring (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pkl_id INT NOT NULL,
  tanggal DATE NOT NULL,
  catatan TEXT NOT NULL,
  tindak_lanjut TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_monitoring_pkl FOREIGN KEY (pkl_id) REFERENCES pkl(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS penilaian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pkl_id INT NOT NULL,
  aspek VARCHAR(100) NOT NULL,
  nilai DECIMAL(5,2) NOT NULL,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_penilaian_pkl FOREIGN KEY (pkl_id) REFERENCES pkl(id) ON DELETE CASCADE
);

-- Login awal: username admin, password password
-- Hash bcrypt untuk password "password"
INSERT INTO admins (nama, username, password)
VALUES ('Administrator Hubin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.')
ON DUPLICATE KEY UPDATE username = VALUES(username);
