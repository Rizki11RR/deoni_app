# ARCHITECTURE — Rekomendasi Stack

## Rekomendasi utama
Bangun **modular monolith** sebagai aplikasi web mandiri:
- Backend: Laravel, gunakan rilis stabil yang masih didukung dan kompatibel dengan PHP.
- Bahasa: PHP.
- Database: PostgreSQL.
- ORM/migration: Eloquent ORM dan Laravel Migrations.
- UI: Livewire custom + Tabler langsung berbasis Bootstrap 5.
- Admin/master data dan workflow order/kasir dibuat custom dengan Livewire; Filament dan Tailwind CSS bukan UI utama.
- Tabler digunakan langsung sebagai UI kit Bootstrap 5 melalui Vite.
- Asset build: Vite.
- Authorization: Laravel Policies/Gates; gunakan role-permission package bila kebutuhan sudah jelas.
- Testing: Pest atau PHPUnit, pilih satu.
- PDF/print: mulai dari HTML + CSS print; tambahkan generator PDF jika benar-benar diperlukan.
- Version control: Git.
- Local development: Docker Compose menjalankan Laravel PHP dan PostgreSQL dengan `compose.yaml`.
- Production: Ubuntu Server LTS, Nginx, PHP-FPM, PostgreSQL, HTTPS, firewall, backup off-server.

Kunci versi dependency di `composer.lock`. Saat setup, verifikasi versi Laravel dan PHP yang saling kompatibel serta masih mendapat dukungan resmi. Jangan upgrade major tanpa audit.

## Alasan
- Pemilik proyek sudah memiliki pengalaman PHP/Laravel dan Linux Mint.
- Laravel cocok untuk validasi, workflow bisnis, transaksi DB, autentikasi, queue, dan laporan.
- PostgreSQL menyediakan transaksi konsisten, constraint, dan kemampuan query laporan.
- Livewire memberi UI interaktif tanpa harus memelihara SPA terpisah.
- Filament mempercepat CRUD master data, tetapi layar order/nota dapat dibuat khusus.
- Modular monolith lebih realistis untuk satu developer dan 1–4 pengguna awal daripada microservices.

## Alternatif
- Laravel + Inertia + Vue: jika UI membutuhkan interaksi frontend kompleks dan developer siap mengelola SPA.
- Django + PostgreSQL: alternatif Python, tetapi Laravel lebih pragmatis karena pengalaman yang sudah ada.
- Odoo custom modules: masih mungkin, namun tidak menjadi fondasi jika tujuan adalah sistem mandiri dengan kendali penuh.
- NestJS + React: tidak disarankan sebagai pilihan pertama tanpa kebutuhan yang membenarkan kompleksitas tambahan.

## Domain/modul
Identity & Access, Products & Units, Customers, Pricing, Sales Orders, Payments, Inventory, Delivery, Printing, Reporting, Audit.

## Entitas konseptual
users, roles/permissions, customers, product_categories, products, units, unit_conversions, price_lists/product_prices, sales_orders, sales_order_lines, payments, payment_allocations, refunds, stock_locations, stock_balances, stock_reservations, stock_movements, stock_adjustments, pickings, delivery_notes, delivery_note_lines, document_sequences, audit_logs, tax_profiles/tax_rules.

## Integritas data
- Uang memakai DECIMAL/NUMERIC.
- Operasi order, pembayaran, stock movement, dan sequence memakai DB transaction.
- Sequence aman terhadap concurrent requests.
- Snapshot detail transaksi menjaga histori dari perubahan master.
- Pembayaran bersifat append-only; koreksi melalui reversal.
- Transaksi tidak di-hard-delete.
- State transition eksplisit dan divalidasi di backend.
- Audit actor, timestamp, perubahan penting, alasan.
- Backup DB dan file; lakukan uji restore.

## Status workflow disetujui
Order besar/POS: Draft → Confirmed → Processing/Picking → Ready → Completed atau Cancelled.
Pembayaran: Unpaid → Partially Paid → Paid.
Pengiriman: Not Required atau Awaiting → Prepared → Dispatched → Delivered atau Failed. Pengiriman parsial tidak diperbolehkan.
Retur/refund dapat dibuat sebagian dan dicatat sebagai transaksi koreksi.

## Deployment
Mulai local di Linux Mint. Produksi dapat menggunakan satu VPS Ubuntu Server LTS untuk beban awal; ukuran resource ditentukan setelah uji coba. Wajib HTTPS, firewall, SSH aman, backup di luar VPS, monitoring disk, dan dokumentasi restore.
