Berdasarkan analisis kode website pengaduan mahasiswa dalam workspace Anda, berikut adalah kebutuhan fungsional yang telah diimplementasikan:

## **Kebutuhan Fungsional Website Pengaduan Mahasiswa**

### **1. Manajemen Pengguna**
- **Registrasi Mahasiswa**: Mahasiswa dapat membuat akun baru dengan username, email, dan password
- **Login/Logout**: Sistem autentikasi untuk mahasiswa dan admin
- **Manajemen Session**: Pengelolaan sesi pengguna yang aman
- **Role-based Access**: Pembedaan akses berdasarkan peran (mahasiswa/admin)

### **2. Pengajuan Pengaduan**
- **Form Pengaduan Lengkap**: Mahasiswa dapat mengajukan pengaduan dengan:
  - Judul pengaduan
  - Deskripsi lengkap
  - Tanggal kejadian
  - Lokasi kejadian
  - Kategori (Fasilitas, Akademik, Layanan, Keuangan, Lainnya)
  - Upload lampiran (foto/dokumen)
- **Data Pelapor Opsional**: Pilihan untuk menyertakan atau menyembunyikan data diri
- **Tipe Pengaduan**: Pilihan antara pengaduan private atau public
- **Pengaduan Anonim**: Memungkinkan pengaduan tanpa login

### **3. Pengelolaan Pengaduan Mahasiswa**
- **Dashboard Mahasiswa**: Melihat semua pengaduan yang telah diajukan
- **Detail Pengaduan**: Melihat detail lengkap pengaduan termasuk status dan balasan
- **Tracking Status**: Memantau perkembangan status pengaduan
- **Riwayat Pengaduan**: Melihat histori semua pengaduan yang pernah dibuat

### **4. Fitur Admin**
- **Dashboard Admin**: Overview statistik jumlah pengaduan dan pengguna
- **Manajemen Pengaduan**:
  - Melihat semua pengaduan (private dan public)
  - Update status pengaduan (Baru, Diproses, Selesai, Ditolak)
- **Manajemen Pengguna**:
  - Melihat daftar semua pengguna
  - Menghapus pengguna (kecuali admin)

### **5. Pengaduan Publik**
- **Halaman Pengaduan Publik**: Menampilkan pengaduan yang bersifat publik
- **Akses Umum**: Pengaduan publik dapat dilihat tanpa login
- **Transparansi**: Memungkinkan mahasiswa lain melihat dan belajar dari pengaduan serupa

### **6. Keamanan dan Validasi**
- **Input Validation**: Validasi semua input form
- **Session Management**: Pengelolaan sesi yang aman
- **File Upload Security**: Penanganan upload file yang aman
- **Access Control**: Kontrol akses berdasarkan role dan ownership

### **7. User Interface**
- **Responsive Design**: Menggunakan Bootstrap untuk tampilan responsif
- **Navigation Menu**: Menu navigasi yang berbeda untuk setiap role
- **Status Indicators**: Badge warna untuk status pengaduan
- **Form Validation**: Validasi client-side dan server-side

### **8. Manajemen Data**
- **Data Pelapor**: Penyimpanan data pribadi (nama, NIM, email, WhatsApp, jenis kelamin)
- **Kategorisasi**: Pengelompokan pengaduan berdasarkan kategori
- **Timestamping**: Pencatatan waktu untuk semua aktivitas
- **File Management**: Pengelolaan lampiran dokumen/foto

### **9. Workflow Pengaduan**
- **Status Tracking**: Alur status dari Baru → Diproses → Selesai/Ditolak

