# TASK — Roadmap

## Phase 0: Discovery dan fondasi
- [x] Audit repository dan environment.
- [x] Jawab keputusan bisnis prioritas.
- [x] Finalisasi stack.
- [x] Finalisasi versi PHP/Laravel/dependency.
- [x] Buat aplikasi, koneksi PostgreSQL, `.env.example`.
- [x] Setup Git, formatter, test runner.
- [x] Login, user, role, permission.

## Phase 1: Master data
- [x] Produk dan kategori.
- [x] Pelanggan.
- [x] Satuan dan konversi yang disetujui.
- [ ] Harga Toko dan Hotel.
- [x] Import produk dengan preview dan validasi.

## Phase 2: Penjualan
- [x] Draft order dan item.
- [x] Tipe harga/dokumen.
- [x] Snapshot harga dan detail produk.
- [x] Diskon nominal/persentase tanpa batas.
- [x] Pajak 11% termasuk harga.
- [x] Sequence dokumen angka tanpa duplikasi, melanjutkan sequence lama.
- [x] Cetak nota dua salinan.
- [x] Audit perubahan dan state transition.

## Phase 3: Pembayaran
- [x] Pembayaran dan metode.
- [x] Parsial/bon dan multi-metode.
- [x] Status lunas dan saldo.
- [x] Reversal dengan otorisasi dan audit.
- [x] Hak akses kasir.

## Phase 4: Gudang dan pengiriman
- [x] Daftar picking.
- [x] Status persiapan.
- [x] Surat Jalan dan sequence.
- [x] Ambil sendiri/kirim.
- [x] Stock movement dengan stok fisik, cadangan, dan tersedia.
- [x] Stocktake dan rekonsiliasi bertahap.

## Phase 5: Laporan dan hardening
- [x] Laporan penjualan/pembayaran.
- [x] Piutang/bon tanpa limit, jatuh tempo opsional, reminder overdue.
- [x] Audit log viewer.
- [x] Backup lokal dan uji restore.
- [ ] Uji hak akses, concurrency, sequence.
- [ ] Uji printer/kertas nyata.
- [ ] UAT dengan staf.
- [x] Dokumentasi deployment shared hosting.

## MVP prioritas
Master data → nota besar/POS → pembayaran → cetak nota. Reservasi stok dan movement dasar masuk MVP; stocktake lanjutan menyusul setelah data stok awal siap.
