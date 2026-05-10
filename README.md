# Neon Quiz - Sistem Buzzer Kuis Real-Time Profesional

**Neon Quiz** adalah platform manajemen kuis interaktif berbasis jaringan lokal (LAN) yang dirancang untuk menghadirkan pengalaman *Game Show* televisi ke acara Anda. Dengan sinkronisasi *real-time* menggunakan WebSockets, aplikasi ini memastikan kecepatan respons milidetik antara pemain dan sistem.

---

## 🏛️ Arsitektur Sistem
Aplikasi ini terdiri dari tiga antarmuka utama yang bekerja secara harmonis:

1.  **Layar Proyektor (`/display`)**: Antarmuka publik untuk audiens yang menampilkan timer, skor, animasi klasemen, dan efek suara.
2.  **Remote Admin (`/admin`)**: Pusat kendali rahasia bagi Game Master untuk mengelola sesi, memutar lagu, dan memberikan poin.
3.  **HP Pemain (`/`)**: Antarmuka sederhana bagi peserta yang berfungsi sebagai tombol buzzer fisik di smartphone mereka.

---

## 🔥 Fitur Unggulan

### 🛡️ Keamanan & Anti-Curang
* **1 Device = 1 Player**: Menggunakan sistem *UUID & Cookie Fingerprinting* untuk mencegah pemain membuka banyak tab atau mengganti nama di tengah permainan.
* **Lobby Locking**: Admin dapat mengunci pendaftaran pemain saat game dimulai untuk mencegah penyusup.
* **Audit Log (VAR System)**: Mencatat setiap aktivitas secara detail (termasuk *reaction time* milidetik) untuk membuktikan siapa yang menekan bel lebih dulu secara adil.

### 🎯 Mode Permainan
* **Mode 1: Rebutan Standar**: Kuis tanya-jawab klasik dengan dukungan *Multiplier* poin (1x, 2x, 3x).
* **Mode 2: Tebak Lagu (YouTube Music)**: Integrasi langsung dengan Playlist YouTube. Sistem akan memotong lagu secara otomatis (2s, 5s, 10s) dan menjeda audio seketika saat bel ditekan.
* **Smart Auto-Skip**: Mendeteksi otomatis jika lagu YouTube diblokir hak cipta pemutaran (*Embed Disabled*) dan melompat ke lagu berikutnya tanpa mengganggu jalannya kuis.

### ✨ Estetika Visual & Audio
* **Animasi Klasemen Mulus**: Pergerakan peringkat menggunakan *GPU-accelerated CSS* (translateY) untuk efek melayang yang dramatis saat pemain menyalip posisi lain.
* **Web Audio Engine**: Menghasilkan suara detak jantung, bel kuis, dan waktu habis langsung dari browser tanpa bergantung pada file MP3 eksternal.
* **State-Driven UI**: Warna layar berubah secara dinamis (Hijau untuk benar, Merah untuk salah) memberikan umpan balik visual instan bagi penonton.

### ⚙️ Kustomisasi Tanpa Kode
* **Halaman Pengaturan**: Admin dapat mengubah durasi timer rebutan, timer menjawab, jumlah poin, hingga mode start timer (saat lagu diputar atau setelah lagu habis) langsung dari database.

---

## 🚀 Teknologi yang Digunakan
* **Backend**: Laravel 11+
* **Real-time**: Laravel Reverb (WebSockets)
* **Frontend**: Tailwind CSS, Alpine.js, Axios
* **Database**: PostgreSQL / MySQL
* **API**: YouTube IFrame Player API

---

## 🛠️ Cara Menjalankan (Development)
1. Clone repositori ini.
2. Jalankan `composer install` dan `npm install`.
3. Konfigurasi `.env` (pastikan REVERB diaktifkan).
4. Jalankan migrasi: `php artisan migrate`.
5. Jalankan server: `php artisan serve` dan `php artisan reverb:start`.
6. Akses `http://localhost:8000/display` untuk layar utama.

---
*Dibuat dengan dedikasi untuk pengalaman kuis yang adil, cepat, dan menyenangkan.*
