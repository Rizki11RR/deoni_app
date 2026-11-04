# PROGRESS

## Status
Discovery selesai dan keputusan bisnis utama disetujui pada 2026-11-03. Fondasi Laravel dan Docker selesai; PostgreSQL aktif dan terhubung; Identity & Access selesai; master Produk, Kategori, dan Pelanggan selesai; fondasi Order/Nota dan sequence dokumen aman selesai; pembayaran dasar dan reversal selesai; audit activity, soft delete seluruh model, halaman error global, dan stok fisik/reservasi selesai; pembatalan order melepas reservasi; audit log viewer dan stocktake rekonsiliasi selesai.

## Fakta konteks
- Usaha: toko bahan kue.
- Sistem diharapkan membantu order/nota, pembayaran, gudang, stok, dan pengiriman/pengambilan.
- Alur lama melibatkan WhatsApp Admin, staf depan, nota beberapa salinan, kasir, gudang, lalu pengiriman/pengambilan.
- Tipe harga Toko dan Hotel diperlukan. Harga Hotel selalu 10% lebih tinggi dari harga Toko.
- Kebutuhan dokumen: Nota Toko, Nota Hotel, kemungkinan Nota Kecil, Surat Jalan.
- Nota pelanggan dan arsip pemilik pernah menjadi kebutuhan.
- Stok dimigrasikan bertahap karena data belum seluruhnya terverifikasi.
- Pajak 11% termasuk harga, bon/pembayaran parsial, multi-metode, aturan reservasi stok, diskon tanpa batas, dan tidak ada pembulatan Rp500 telah disetujui.
- Perkiraan pengguna awal 1–4.
- Pemilik proyek ingin membangun sendiri dengan bantuan AI coding agent.
- Lingkungan yang diketahui: Linux Mint 22.3, Docker, Git, dan pengalaman PHP/Laravel. Ada setup Odoo 19 terdahulu, tetapi aplikasi mandiri tidak harus bergantung pada Odoo.

## Stack disetujui
Laravel + PostgreSQL + Livewire + Tabler + Bootstrap 5 + Vite. Modular monolith dengan workflow nota besar dan POS custom; Filament dan Tailwind bukan UI utama.

## Belum diverifikasi
Nomor terakhir tiap sequence lama, data master produk/pelanggan, data stok awal, volume transaksi, dan target deployment. Laravel `v13.39.0`, Livewire `v4.4.8`, PHP requirement `^8.3`, Bootstrap 5 via Tabler, dan dependency terkunci di lock file.

## Riwayat
Baseline konteks dibuat sebagai paket awal untuk AI agent.
