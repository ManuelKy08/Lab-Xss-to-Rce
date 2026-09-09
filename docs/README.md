# Security Lab - XSS to RCE

Laboratorium pembelajaran keamanan web (white-hat/ethical hacking) yang menyajikan
rantai serangan **XSS hingga RCE**. Semua fitur **hanya untuk localhost**, tidak untuk
lingkungan produksi, dan tidak untuk menyerang sistem lain.

> &#9888; **PERINGATAN:** Lab ini sengaja dibuat rentan untuk tujuan belajar.
> Jangan pernah men-deploy ke server publik.

---

## Fitur

- **XSS Reflected** - input pencarian direfleksikan tanpa sanitasi
- **XSS Stored** - komentar disimpan & dirender mentah (persistent)
- **XSS DOM-based** - manipulasi `innerHTML` dari URL hash (client-side)
- **File Upload to RCE** - upload file `.php` (&rarr; webshell)
- **Command Injection** - `ping` tool dengan input tanpa sanitasi
- **Eval Injection** - kalkulator menggunakan `eval()` mentah
- **Cookie Stealer** - endpoint penangkap `document.cookie` dari payload XSS
- **Dashboard** - statistik, log aktivitas, request/response headers, cookie tertangkap
- **Toggle Security** - bandingkan mode *Vulnerable* vs *Secure* langsung dari kode
- **Reset Lab** - kembalikan seluruh state ke kondisi awal
- **Challenge Mode** - 3 level: XSS reflected &rarr; cookie stealing &rarr; RCE
- **Burp Suite Ready** - tampilkan raw request, copy ke Repeater

## Persyaratan

- PHP 8.x (dengan ekstensi `sqlite3` dan `pdo_sqlite`) - tersedia di XAMPP
- Browser modern
- (Opsional) Docker untuk menjalankan via `docker-compose.yml`

## Menjalankan

### Opsi 1: PHP Built-in Server

```bash
php -S 127.0.0.1:8080 -t public
```

Buka `http://127.0.0.1:8080`.

### Opsi 2: XAMPP

Letakkan folder proyek ke `C:\xampp\htdocs\security-lab`, lalu akses
`http://localhost/security-lab/public`.

### Opsi 3: Docker

```bash
docker compose up -d
```

Buka `http://127.0.0.1:8080`.

## Struktur Folder

```
security-lab/
├── public/            (root web - halaman yang bisa diakses)
│   ├── index.php          (dashboard utama)
│   ├── dashboard.php      (statistik, log, cookie)
│   ├── reset-lab.php      (reset seluruh state)
│   ├── steal-cookie.php   (endpoint cookie stealer)
│   ├── challenge.php      (challenge mode 3 level)
│   ├── xss/               (reflected, stored, dom)
│   ├── rce/               (upload, cmd, eval)
│   ├── uploads/           (tempat file ter-upload)
│   └── assets/            (css/js)
├── includes/          (config, db, functions, layout)
├── database/          (lab.db - SQLite)
├── logs/              (cookies.log, activity.log)
└── docs/              (dokumentasi)
```

## Alur Eksploitasi Singkat

1. Konfirmasi XSS reflected
2. Gunakan stored XSS untuk mencuri cookie session
3. Upload webshell PHP untuk memulai RCE
4. Eksploitasi command injection &amp; eval injection

Lihat [panduan.md](panduan.md) untuk langkah demi langkah.