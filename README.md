# Metalabs Face Recognition

Sistem Absensi Pengenalan Wajah AI (AI Face Recognition) canggih yang dirancang khusus untuk Asisten Laboratorium (Aslab) dan dapat berjalan secara offline.

## 🚀 Fitur Utama

*   **100% AI Lokal:** Menggunakan WebGL dan TensorFlow.js (via `face-api.js`) untuk memproses pengenalan wajah sepenuhnya di dalam browser. Tidak ada gambar atau data biometrik yang dikirim ke server cloud eksternal.
*   **Pelacakan Waktu Pintar (Smart Timeframe):** Secara otomatis memeriksa wajah yang dipindai dengan jadwal hari itu. Mencatat kehadiran secara akurat sebagai "On Time" (Tepat Waktu), "Late" (Terlambat), atau menolak pemindaian untuk Aslab yang tidak memiliki jadwal.
*   **Dashboard Admin:** Dashboard komprehensif untuk mengelola Aslab, mengatur jadwal shift, dan melihat statistik absensi secara real-time.
*   **Auto-Absent Cron:** Skrip pseudo-cron bawaan yang secara otomatis menandai Aslab menjadi "Absent" (Alpa) jika mereka melewatkan jadwal shift mereka.
*   **Export ke Excel:** Ekspor seluruh log riwayat absensi ke dalam format CSV hanya dengan satu klik.

## 🛠️ Cara Instalasi

1.  **Clone Repository:**
    ```bash
    git clone https://github.com/AryaDanuH/metalabs-face-recognition.git
    cd metalabs-face-recognition
    ```
2.  **Setup Database:**
    *   Buat database MySQL baru dengan nama `metalabs`.
    *   Import file `database_schema.sql` yang telah disediakan untuk mengatur tabel-tabel yang dibutuhkan:
        ```bash
        mysql -u root -p metalabs < database_schema.sql
        ```
3.  **Konfigurasi:**
    *   Pastikan web server Anda (contoh: XAMPP, Apache) mengarah ke direktori yang baru saja di-clone.
    *   Jika kredensial database Anda berbeda dari default (user: `root` / password kosong), silakan perbarui file `api/db.php`.
4.  **Jalankan Aplikasi:**
    *   Akses aplikasi melalui server lokal Anda (contoh: `http://localhost/metalabs`).

## 🛡️ Privasi & Keamanan

Sistem ini dirancang khusus untuk menangani data biometrik sensitif secara bertanggung jawab. Face descriptors (array matematis yang merepresentasikan fitur wajah) dihitung secara lokal dan disimpan dengan aman di dalam database MySQL. Rekaman video dan gambar asli dari Live Scanner akan langsung dihapus dan tidak pernah disimpan atau ditransmisikan kemana pun.

## 📄 Lisensi

Proyek ini bersifat open-source dan tersedia di bawah [MIT License](LICENSE).
