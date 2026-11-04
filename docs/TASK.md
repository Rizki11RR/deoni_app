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
- [ ] Satuan dan konversi yang disetujui.
- [ ] Harga Toko dan Hotel.
- [ ] Import produk dengan preview dan validasi.

## Phase 2: Penjualan
- [x] Draft order dan item.
- [x] Tipe harga/dokumen.
- [x] Snapshot harga dan detail produk.
- [ ] Diskon nominal/persentase tanpa batas.
- [x] Pajak 11% termasuk harga.
- [x] Sequence dokumen angka tanpa duplikasi, melanjutkan sequence lama.
- [ ] Cetak nota dua salinan.
- [x] Audit perubahan dan state transition.

## Phase 3: Pembayaran
- [ ] Pembayaran dan metode.
- [ ] Parsial/bon dan multi-metode.
- [ ] Status lunas dan saldo.
- [x] Reversal dengan otorisasi dan audit.
- [ ] Hak akses kasir.

## Phase 4: Gudang dan pengiriman
- [x] Daftar picking.
- [x] Status persiapan.
- [ ] Surat Jalan dan sequence.
- [ ] Ambil sendiri/kirim.
- [x] Stock movement dengan stok fisik, cadangan, dan tersedia.
- [x] Stocktake dan rekonsiliasi bertahap.

## Phase 5: Laporan dan hardening
- [ ] Laporan penjualan/pembayaran.
- [ ] Piutang/bon bila diperlukan.
- [x] Audit log viewer.
- [ ] Backup dan uji restore.
- [ ] Uji hak akses, concurrency, sequence.
- [ ] Uji printer/kertas nyata.
- [ ] UAT dengan staf.
- [ ] Dokumentasi deployment.

## MVP prioritas
Master data → nota besar/POS → pembayaran → cetak nota. Reservasi stok dan movement dasar masuk MVP; stocktake lanjutan menyusul setelah data stok awal siap.
