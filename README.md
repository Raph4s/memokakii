# 🗂️ Memokakii

Aplikasi manajemen tugas (To-Do List) berbasis web, dibangun dengan PHP, MySQL, dan Bootstrap 5 sebagai Final Project mata kuliah Basis Data.

> Memokakii berasal dari kata "Memo" dan bahasa Jepang 書き (*kaki*) yang berarti "menulis" — secara harfiah berarti "menulis catatan".

## ✨ Fitur

- Autentikasi (Register, Login, Logout) dengan password terenkripsi
- CRUD Tugas (Tambah, Lihat, Edit, Hapus) via Bootstrap Modal
- Filter prioritas (Tinggi, Sedang, Rendah) dan status (Pending, In Progress, Selesai)
- Search dan pagination
- Progress bar persentase tugas selesai
- Deteksi tugas overdue otomatis
- Dark mode
- Floating audio player

## 🗄️ Implementasi Database

| Komponen | Jumlah | Detail |
|----------|--------|--------|
| Tabel | 5 | users, kategori, tasks, task_log, notifikasi |
| Complex Query | 3 | JOIN, Aggregasi, JOIN + WHERE |
| View | 2 | v_task_summary, v_task_overdue |
| Function | 2 | persen_selesai(), label_prioritas() |
| Trigger | 2 | after_task_insert, after_task_update |

## 🛠️ Tech Stack

- PHP 8+
- MySQL 8
- Bootstrap 5.3
- Bootstrap Icons
- Laragon (local development)

## ⚙️ Cara Install

1. Clone repo ini ke folder Laragon:
```bash
   git clone https://github.com/Raph4s/memokakii.git C:\laragon\www\memokakii
```

2. Buka phpMyAdmin → buat database `todo_pbd`

3. Jalankan query SQL berikut secara berurutan di phpMyAdmin untuk membuat tabel, view, function, trigger, dan data awal.

4. Buat file `config/db.php`:
```php
   <?php
   $host = 'localhost';
   $db   = 'todo_pbd';
   $user = 'root';
   $pass = '';
   
   try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
     die("Koneksi gagal: " . $e->getMessage());
   }
```

5. Buka browser: `http://localhost/memokakii`

## 🔑 Akun Default

| Email | Password |
|-------|----------|
| raffa@mail.com | password |

## 👤 Author

**Raffa Ramadhika** — 25/560643/SV/26459  
D4 Teknologi Rekayasa Perangkat Lunak  
Universitas Gadjah Mada
