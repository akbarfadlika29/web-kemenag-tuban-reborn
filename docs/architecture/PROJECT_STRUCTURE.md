# WEB PPID — Struktur Proyek

Struktur ini memisahkan kode berdasarkan tanggung jawab agar mudah dirawat.

## Aplikasi Laravel

- `app/Http/Controllers/Admin` — controller area admin per modul.
- `app/Http/Controllers/Frontend` — controller publik.
- `app/Http/Middleware` — middleware otorisasi dan proteksi request.
- `app/Models` — model Eloquent.
- `app/Services` — business/service layer per domain.
- `app/Support` — helper/support class lintas modul.

## View

- `resources/views/admin` — seluruh view admin per modul.
- `resources/views/frontend` — seluruh view publik per modul.
- `resources/views/frontend/home/partials` — potongan khusus halaman Home.
- `resources/views/components` — Blade component reusable.
- `resources/views/layouts` — layout utama admin/auth.

## Asset frontend

- `public/css/frontend/base` — token, reset, typography, utilities.
- `public/css/frontend/layout` — header, navigasi, footer, responsive layout.
- `public/css/frontend/components` — komponen reusable.
- `public/css/frontend/pages` — style khusus halaman/fitur.
- `public/css/frontend/pages/home` — seluruh style khusus Home.
- `public/css/frontend/pages/ppid` — seluruh style khusus direktori PPID.
- `public/css/frontend/shared` — lapisan konsistensi lintas halaman yang masih aktif.
- `public/css/frontend/themes` — lapisan tema visual.
- `public/js/frontend/components` — behavior reusable.
- `public/js/frontend/pages/home` — JavaScript yang hanya dipakai Home.

## Asset admin

- `public/css/admin/base` — fondasi CSS admin.
- `public/css/admin/layout` — shell, sidebar, navbar, footer.
- `public/css/admin/components` — komponen UI reusable.
- `public/css/admin/pages` — style khusus halaman admin.
- `public/css/admin/themes` — tema visual admin.
- `public/js/admin/app.js` — behavior global admin.
- `public/js/admin/components` — JavaScript komponen reusable.
- `public/js/admin/layout` — behavior layout/sidebar.
- `public/js/admin/pages` — JavaScript khusus halaman/modul admin.

## Route

- `routes/web.php` — route publik utama.
- `routes/frontend` — route publik tambahan per fitur.
- `routes/admin.php` — group dan middleware admin.
- `routes/admin` — route tiap modul admin.

## Aturan penempatan

1. File khusus satu halaman tidak boleh diletakkan di root asset.
2. File reusable masuk `components`.
3. File navigasi/layout masuk `layout`.
4. File tema tidak boleh bercampur dengan komponen.
5. Jangan menyimpan `.backup`, `.old`, atau file percobaan di folder aplikasi aktif.
6. JavaScript admin tidak boleh berada di `public/js/modules` generik; semua berada di `public/js/admin`.
7. Route admin harus berada di `routes/admin`, bukan folder generik `modules`.
8. Sebelum menghapus file, pastikan tidak ada referensi Blade/PHP/CSS/JS yang aktif.
