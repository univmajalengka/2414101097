# 📂 LAPORAN ANALISIS DAN RESOLUSI INSIDEN APLIKASI REGISTRASI SISWA

**ID Insiden:** APP-REG-2025-001
**Modul Terdampak:** Subsistem Registrasi Data (`proses-pendaftaran-2.php`) dan Konektivitas Database (`koneksi.php`).
**Status:** **RESOLVED**

---

## I. LOG INSIDEN CRITICAL (Phase 1: Deployment Awal)

### A. INSIDEN SINTAKSIS (T_STRING UNEXPECTED)

| ID Error | Deskripsi Insiden | Modul | Lokasi | Tingkat Keparahan |
| :--- | :--- | :--- | :--- | :--- |
| PHP-P001 | `Parse error: syntax error, unexpected 'sekolah' (T_STRING)` | `proses-pendaftaran-2.php` | Line 12 | Critical |

#### Analisis Akar Masalah (RCA)
Kegagalan parsing PHP dikarenakan variabel lokal (`sekolah`) digunakan tanpa deklarasi penanda variabel (`$`). Interpreter PHP gagal mengidentifikasi entitas tersebut sebagai variabel.

#### Aksi Korektif
Dilakukan *patching* untuk implementasi standar deklarasi variabel.
* **Perubahan:** `sekolah = $_POST['sekolah_asal'];` menjadi `$sekolah = $_POST['sekolah_asal'];`

### B. INSIDEN INTEGRITAS SQL (CODE 1064)

| ID Error | Deskripsi Insiden | Modul | Lokasi | Tingkat Keparahan |
| :--- | :--- | :--- | :--- | :--- |
| SQL-E1064 | `Warning: mysqli_query(): (21000/1064): Syntax error in SQL statement...` | `proses-pendaftaran-2.php` | Line 17 | Major |

#### Analisis Akar Masalah (RCA)
Kesalahan sintaksis pada *query* `INSERT`. Klausa untuk mendefinisikan nilai data adalah `VALUES` (jamak) sesuai standar SQL, namun diimplementasikan sebagai `VALUE` (tunggal).

#### Aksi Korektif
Dilakukan modifikasi pada konstruksi *query* DML.
* **Perubahan:** Mengganti *keyword* SQL `VALUE` menjadi `VALUES` dalam pernyataan `INSERT`.

---

## II. LOG INSIDEN FATAL (Phase 2: Execution Time)

### A. INSIDEN OTENTIKASI DATABASE (ACCESS DENIED)

| ID Error | Deskripsi Insiden | Modul | Lokasi | Tingkat Keparahan |
| :--- | :--- | :--- | :--- | :--- |
| DB-F001 | `Fatal error: Uncaught mysqli_sql_exception: Access denied for user 'root'@'localhost' (using password: YES)` | `koneksi.php` | Line 9 | Fatal |

#### Analisis Akar Masalah (RCA)
Gagalnya inisiasi koneksi *database* (`mysqli_connect()`) karena *credential* yang disediakan tidak valid. Konfigurasi default XAMPP menetapkan *password* `root` sebagai string kosong (`""`), sementara *script* mengimplementasikan *password* statis yang salah (`"12345"`).

#### Aksi Korektif
Dilakukan penyesuaian konfigurasi *credential* di layer konektivitas.

```php
// koneksi.php (Aksi Korektif)
$password = ""; // Disesuaikan dengan konfigurasi default MySQL pada lingkungan XAMPP