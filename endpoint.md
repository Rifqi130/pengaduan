Berdasarkan analisis kode dalam workspace Anda, berikut adalah endpoint REST API yang diperlukan untuk sistem pengaduan mahasiswa:

## **Endpoint REST API yang Diperlukan**

### **1. Authentication Endpoints**

#### `POST /api/auth/register`

**Request:**

```json
{
  "username": "string",
  "email": "string",
  "password": "string"
}
```

**Response (201):**

```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "user_id": 123,
    "username": "mahasiswa1",
    "email": "mhs1@example.com",
    "role": "mahasiswa"
  }
}
```

#### `POST /api/auth/login`

**Request:**

```json
{
  "username": "string",
  "password": "string",
  "role": "mahasiswa|admin"
}
```

**Response (200):**

```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "token": "jwt_token_here",
    "user": {
      "id": 123,
      "username": "mahasiswa1",
      "role": "mahasiswa"
    }
  }
}
```

#### `POST /api/auth/logout`

**Response (200):**

```json
{
  "status": "success",
  "message": "Logout successful"
}
```

### **2. Complaint Endpoints**

#### `GET /api/complaints`

**Query Parameters:** `?type=public|private&status=Baru|Diproses|Selesai|Ditolak`
**Response (200):**

```json
{
  "status": "success",
  "data": {
    "complaints": [
      {
        "id": 1,
        "title": "Fasilitas Kelas Tidak Lengkap",
        "description": "Proyektor di kelas A.101 rusak...",
        "category": "Fasilitas",
        "status": "Baru",
        "tipe_aduan": "private",
        "tanggal_kejadian": "2025-05-30",
        "lokasi_kejadian": "Kelas A.101",
        "nama_pelapor": "Mahasiswa Satu",
        "date_posted": "2025-06-01 10:30:00",
        "last_updated": "2025-06-01 10:30:00"
      }
    ]
  }
}
```

#### `POST /api/complaints`

**Request (multipart/form-data):**

```json
{
  "title": "string",
  "description": "string",
  "category": "Fasilitas|Akademik|Layanan|Keuangan|Lainnya",
  "tanggal_kejadian": "2025-06-01",
  "lokasi_kejadian": "string",
  "tipe_aduan": "private|public",
  "sertakan_data_diri": true,
  "nama_pelapor": "string (optional)",
  "jenis_kelamin": "Laki-laki|Perempuan (optional)",
  "nim": "string (optional)",
  "whatsapp": "string (optional)",
  "email_pelapor": "string (optional)",
  "lampiran": "file (optional)"
}
```

**Response (201):**

```json
{
  "status": "success",
  "message": "Complaint submitted successfully",
  "data": {
    "complaint_id": 456,
    "title": "Fasilitas Kelas Tidak Lengkap",
    "status": "Baru",
    "date_posted": "2025-06-08 14:30:00"
  }
}
```

**Response (403 - User Inactive):**

```json
{
  "status": "error",
  "message": "Akun Anda tidak aktif. Silakan hubungi admin.",
  "code": "ACCOUNT_INACTIVE"
}
```

#### `GET /api/complaints/{id}`

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Fasilitas Kelas Tidak Lengkap",
    "description": "Proyektor di kelas A.101 rusak dan tidak bisa digunakan untuk presentasi.",
    "category": "Fasilitas",
    "status": "Baru",
    "tipe_aduan": "private",
    "tanggal_kejadian": "2025-05-30",
    "lokasi_kejadian": "Kelas A.101",
    "nama_pelapor": "Mahasiswa Satu",
    "jenis_kelamin": "Laki-laki",
    "nim": "12345678",
    "whatsapp": "081234567890",
    "email_pelapor": "mhs1@example.com",
    "lampiran": "photo_evidence.jpg",
    "date_posted": "2025-06-01 10:30:00",
    "last_updated": "2025-06-01 10:30:00"
  }
}
```

#### `PUT /api/complaints/{id}/status` (Admin only)

**Request:**

```json
{
  "status": "Baru|Diproses|Selesai|Ditolak"
}
```

**Response (200):**

```json
{
  "status": "success",
  "message": "Complaint status updated successfully",
  "data": {
    "complaint_id": 1,
    "new_status": "Diproses",
    "updated_at": "2025-06-08 15:45:00"
  }
}
```

### **3. User Management Endpoints**

#### `GET /api/users/me`

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "id": 123,
    "username": "mahasiswa1",
    "email": "mhs1@example.com",
    "role": "mahasiswa",
    "full_name": "Mahasiswa Satu",
    "nim": "12345678",
    "created_at": "2025-05-01 09:00:00"
  }
}
```

#### `GET /api/users/me/complaints`

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "complaints": [
      {
        "id": 1,
        "title": "Fasilitas Kelas Tidak Lengkap",
        "category": "Fasilitas",
        "status": "Baru",
        "tipe_aduan": "private",
        "date_posted": "2025-06-01 10:30:00"
      }
    ],
    "total_complaints": 1
  }
}
```

#### `GET /api/admin/users` (Admin only)

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "users": [
      {
        "id": 123,
        "username": "mahasiswa1",
        "email": "mhs1@example.com",
        "role": "mahasiswa",
        "full_name": "Mahasiswa Satu",
        "created_at": "2025-05-01 09:00:00",
        "is_active": true
      }
    ],
    "total_users": 3
  }
}
```

#### `DELETE /api/admin/users/{id}` (Admin only)

**Response (200):**

```json
{
  "status": "success",
  "message": "User deleted successfully"
}
```

### **4. Categories Endpoint**

#### `GET /api/categories`

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Fasilitas",
        "description": "Pengaduan terkait fasilitas kampus"
      },
      {
        "id": 2,
        "name": "Akademik",
        "description": "Pengaduan terkait kegiatan akademik"
      }
    ]
  }
}
```

### **5. Dashboard/Statistics Endpoints**

#### `GET /api/admin/dashboard` (Admin only)

**Response (200):**

```json
{
  "status": "success",
  "data": {
    "total_complaints": 25,
    "complaints_by_status": {
      "Baru": 5,
      "Diproses": 10,
      "Selesai": 8,
      "Ditolak": 2
    },
    "complaints_by_category": {
      "Fasilitas": 8,
      "Akademik": 7,
      "Layanan": 5,
      "Keuangan": 3,
      "Lainnya": 2
    },
    "total_users": 15,
    "recent_complaints": [
      {
        "id": 25,
        "title": "Pengaduan Terbaru",
        "status": "Baru",
        "date_posted": "2025-06-08 14:30:00"
      }
    ]
  }
}
```

### **6. Error Response Format**

Semua error menggunakan format standar:

```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

**HTTP Status Codes:**

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

Struktur API ini mempertahankan semua fungsionalitas dari sistem web yang ada sambil menyediakan interface REST yang standar dan mudah digunakan.
