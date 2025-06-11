# Database Schema untuk Sistem Pengaduan Mahasiswa (Simplified)

## Overview
Database ini dirancang untuk mendukung sistem pengaduan mahasiswa dengan fitur-fitur berikut:
- Autentikasi pengguna (Admin dan Mahasiswa)
- Pengajuan pengaduan (anonim atau dengan data diri)
- Manajemen status pengaduan
- Upload lampiran
- Kategorisasi pengaduan
- Pengaduan publik dan privat

**CATATAN**: Versi ini adalah versi yang disederhanakan tanpa fitur:
- Sistem balasan/komentar
- Riwayat status
- Penugasan ke petugas tertentu

## Database Schema

### 1. Tabel `users`
Menyimpan data pengguna sistem (Admin dan Mahasiswa).

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
    full_name VARCHAR(100),
    nim VARCHAR(20),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
```

### 2. Tabel `categories`
Menyimpan kategori pengaduan.

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Tabel `complaints` (Simplified)
Menyimpan data pengaduan mahasiswa.

```sql
CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- NULL untuk pengaduan anonim
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Data Pelapor (opsional, bisa NULL jika tidak menyertakan data diri)
    nama_pelapor VARCHAR(100),
    jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
    nim VARCHAR(20),
    whatsapp VARCHAR(20),
    email_pelapor VARCHAR(100),
    
    -- Data Kejadian
    tanggal_kejadian DATE NOT NULL,
    lokasi_kejadian VARCHAR(255) NOT NULL,
    
    -- Status (simplified - no assignment)
    status ENUM('Baru', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Baru',
    tipe_aduan ENUM('private', 'public') DEFAULT 'private',
    
    -- Lampiran
    lampiran VARCHAR(255), -- Nama file lampiran
    lampiran_path VARCHAR(500), -- Path lengkap file
    
    -- Timestamp
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_tipe_aduan (tipe_aduan),
    INDEX idx_date_posted (date_posted)
);
```

### 4. Tabel `complaint_attachments` (Optional)
Menyimpan informasi detail lampiran (untuk multiple files di masa depan).

```sql
CREATE TABLE complaint_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_complaint_id (complaint_id)
);
```

## Data Awal (Seeding)

### Categories
```sql
INSERT INTO categories (name, description) VALUES 
('Fasilitas', 'Pengaduan terkait fasilitas kampus'),
('Akademik', 'Pengaduan terkait kegiatan akademik'),
('Layanan', 'Pengaduan terkait layanan kampus'),
('Keuangan', 'Pengaduan terkait keuangan dan pembayaran'),
('Lainnya', 'Pengaduan kategori lainnya');
```

### Default Admin User
```sql
INSERT INTO users (username, email, password, role, full_name) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator');

-- Password: admin123 (hashed dengan bcrypt)
```

## Indexes dan Optimasi

### Indexes Tambahan untuk Performa
```sql
-- Composite index untuk query kompleks
CREATE INDEX idx_complaints_status_type_date ON complaints(status, tipe_aduan, date_posted);
CREATE INDEX idx_complaints_user_status ON complaints(user_id, status);

-- Full-text search untuk pencarian
ALTER TABLE complaints ADD FULLTEXT(title, description);
```

## Views untuk Kemudahan Query (Simplified)

### View Pengaduan Publik
```sql
CREATE VIEW public_complaints AS 
SELECT 
    c.*,
    cat.name as category_name,
    u.username
FROM complaints c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN users u ON c.user_id = u.id
WHERE c.tipe_aduan = 'public'
ORDER BY c.date_posted DESC;
```

### View Pengaduan dengan Statistik
```sql
CREATE VIEW complaint_stats AS
SELECT 
    c.*,
    cat.name as category_name,
    u.username,
    u.full_name
FROM complaints c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN users u ON c.user_id = u.id;
```

## Considerations & Best Practices

1. **Security**:
   - Password di-hash menggunakan bcrypt
   - Validasi input untuk mencegah SQL injection
   - File upload validation untuk keamanan

2. **Privacy**:
   - Pengaduan anonim (user_id = NULL)
   - Tipe aduan private/public
   - Data pelapor opsional

3. **Scalability**:
   - Indexes yang tepat untuk performa query
   - Partitioning berdasarkan tanggal jika data besar
   - Soft delete untuk audit trail

4. **File Management**:
   - Simpan file di luar web root
   - Gunakan UUID untuk nama file
   - Implementasi cleanup untuk file orphan

5. **Simplified Design**:
   - Fokus pada fitur core tanpa kompleksitas berlebihan
   - Status sederhana tanpa tracking history
   - Direct admin response tanpa sistem assignment

## Struktur Final (Simplified)

Database ini terdiri dari **3 tabel utama**:
1. `users` - Autentikasi dan data pengguna
2. `categories` - Kategorisasi pengaduan
3. `complaints` - Data pengaduan utama

Plus **1 tabel optional**:
4. `complaint_attachments` - Detail lampiran (untuk ekspansi future)