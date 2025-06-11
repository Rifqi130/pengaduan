-- Database Creation Script for Sistem Pengaduan Mahasiswa (Simplified)
-- Created: 2025-06-07
-- Simplified version: No replies, no status history, no assignment

-- Create Database
CREATE DATABASE IF NOT EXISTS pengaduan_mahasiswa 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE pengaduan_mahasiswa;

-- =============================================================================
-- TABLE CREATION
-- =============================================================================

-- 1. Users Table
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

-- 2. Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Complaints Table (Simplified)
CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- NULL untuk pengaduan anonim
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Data Pelapor (opsional)
    nama_pelapor VARCHAR(100),
    jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
    nim VARCHAR(20),
    whatsapp VARCHAR(20),
    email_pelapor VARCHAR(100),
    
    -- Data Kejadian
    tanggal_kejadian DATE NOT NULL,
    lokasi_kejadian VARCHAR(255) NOT NULL,
    
    -- Status (simplified)
    status ENUM('Baru', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Baru',
    tipe_aduan ENUM('private', 'public') DEFAULT 'private',
    
    -- Lampiran
    lampiran VARCHAR(255),
    lampiran_path VARCHAR(500),
    
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

-- 4. Complaint Attachments Table (Optional for future use)
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

-- =============================================================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- =============================================================================

-- Composite indexes untuk query kompleks
CREATE INDEX idx_complaints_status_type_date ON complaints(status, tipe_aduan, date_posted);
CREATE INDEX idx_complaints_user_status ON complaints(user_id, status);

-- Full-text search index
ALTER TABLE complaints ADD FULLTEXT(title, description);

-- =============================================================================
-- VIEWS (SIMPLIFIED)
-- =============================================================================

-- View untuk pengaduan publik (simplified - no replies)
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

-- View untuk statistik pengaduan (simplified - no replies)
CREATE VIEW complaint_stats AS
SELECT 
    c.*,
    cat.name as category_name,
    u.username,
    u.full_name
FROM complaints c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN users u ON c.user_id = u.id;

-- =============================================================================
-- INITIAL DATA (SEEDING)
-- =============================================================================

-- Insert Categories
INSERT INTO categories (name, description) VALUES 
('Fasilitas', 'Pengaduan terkait fasilitas kampus'),
('Akademik', 'Pengaduan terkait kegiatan akademik'),
('Layanan', 'Pengaduan terkait layanan kampus'),
('Keuangan', 'Pengaduan terkait keuangan dan pembayaran'),
('Lainnya', 'Pengaduan kategori lainnya');

-- Insert Default Admin (password: admin123)
INSERT INTO users (username, email, password, role, full_name) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator');

-- Insert Sample Students
INSERT INTO users (username, email, password, role, full_name, nim) VALUES 
('mahasiswa1', 'mhs1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Mahasiswa Satu', '12345678'),
('mahasiswa2', 'mhs2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Mahasiswa Dua', '87654321');

-- Insert Sample Complaints
INSERT INTO complaints (user_id, category_id, title, description, nama_pelapor, jenis_kelamin, nim, whatsapp, email_pelapor, tanggal_kejadian, lokasi_kejadian, tipe_aduan, status) VALUES 
(2, 1, 'Fasilitas Kelas Tidak Lengkap', 'Proyektor di kelas A.101 rusak dan tidak bisa digunakan untuk presentasi.', 'Mahasiswa Satu', 'Laki-laki', '12345678', '081234567890', 'mhs1@example.com', '2025-05-30', 'Kelas A.101', 'private', 'Baru'),
(3, 3, 'Layanan Perpustakaan Lambat', 'Proses peminjaman buku di perpustakaan memakan waktu yang sangat lama.', 'Mahasiswa Dua', 'Perempuan', '87654321', '089876543210', 'mhs2@example.com', '2025-06-01', 'Perpustakaan Pusat', 'public', 'Diproses');

-- =============================================================================
-- END OF SCRIPT
-- =============================================================================
