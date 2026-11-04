# DECISIONS — Register Keputusan

Status: PROPOSED = usulan, OPEN = belum diputuskan, APPROVED = disetujui eksplisit.

Keputusan disetujui pemilik proyek: 2026-11-03.

## Usulan awal
| ID | Topik | Usulan | Status |
|---|---|---|---|
| D-001 | Produk aplikasi | Web app mandiri, bukan Odoo POS sebagai fondasi | APPROVED |
| D-002 | Arsitektur | Modular monolith | APPROVED |
| D-003 | Alur utama | Nota besar dan POS; pembayaran dapat berasal dari transaksi | APPROVED |
| D-004 | Harga | Tipe harga Toko dan Hotel; Hotel selalu 10% di atas Toko | APPROVED |
| D-005 | Audit | Catat aktor, waktu, aksi, perubahan harga, dan alasan koreksi | APPROVED |
| D-006 | Transaksi lunas | Tidak diedit langsung; koreksi melalui reversal/void | APPROVED |
| D-007 | Pajak | Semua produk, 11%, harga sudah termasuk pajak | APPROVED |
| D-008 | Diskon | Nominal dan persentase, tanpa batas, semua staff boleh memberi | APPROVED |
| D-009 | Sequence | Nomor berbeda untuk Nota Toko, Nota Hotel, Nota Sales, Nota Kecil/POS, dan Surat Jalan; angka melanjutkan sequence lama | APPROVED |
| D-010 | Pembulatan | Tidak ada pembulatan Rp500 | APPROVED |
| D-011 | UI | Tabler langsung dengan Bootstrap 5; Livewire custom; bukan Filament/Tailwind sebagai UI utama | APPROVED |
| D-012 | Pelanggan | Nota besar wajib pelanggan; Nota Kecil/POS boleh anonim | APPROVED |
| D-013 | Pembayaran | Parsial, multi-metode, ambil/kirim sebelum lunas; metode Tunai, QRIS, Transfer, Debit | APPROVED |
| D-014 | Retur/refund | Semua kasir boleh menyetujui; retur sebagian diperbolehkan | APPROVED |
| D-015 | Stok | Bedakan fisik, cadangan, tersedia; order mencadangkan, barang keluar mengurangi fisik | APPROVED |
| D-016 | Pengiriman | Pengiriman parsial tidak boleh; Surat Jalan opsional untuk pengiriman; tanda tangan tetap di kertas | APPROVED |
| D-017 | Hak akses | 1 Admin, 1 Owner, 2 Kasir, 2 Penjualan Depan/POS, Gudang; satu orang boleh beberapa peran | APPROVED |
| D-018 | Harga transaksi | Semua staff boleh mengubah harga; perubahan wajib tercatat | APPROVED |
| D-019 | Produk | Kasir boleh melihat dan mengubah produk | APPROVED |

## Pertanyaan bisnis terbuka
1. Apakah pelanggan wajib dipilih atau boleh walk-in tanpa data pelanggan?
2. Apakah harga Hotel selalu 10% di atas Toko? Bisa override per produk/pelanggan? Bagaimana pembulatannya?
3. Apakah order boleh diedit setelah dikonfirmasi tetapi sebelum dibayar?
4. Apakah pembayaran parsial dan bon diperbolehkan? Siapa yang berwenang?
5. Pembayaran dilakukan sebelum picking, sesudah picking, atau fleksibel?
6. Apakah pengiriman parsial dari satu order diperbolehkan?
7. Kapan stok direservasi/dikurangi: saat order, picking, barang keluar, atau diterima?
8. Apakah stok awal harus dikontrol atau sementara hanya dicatat sebagai informasi?
9. Nota Kecil berbeda aturan dari Nota Toko/Hotel atau hanya format cetak?
10. Nomor dokumen reset per tahun? Format final apa?
11. Produk/transaksi mana yang dikenai pajak? Harga termasuk atau belum termasuk pajak?
12. Siapa boleh memberi diskon? Perlu batas atau approval?
13. Pembulatan Rp500 berlaku semua metode atau hanya tunai? Selalu ke atas?
14. Metode pembayaran: tunai, transfer, QRIS, debit, lainnya?
15. Perlu retur barang/refund?
16. Satu order boleh dibayar dengan beberapa metode?
17. Jumlah staf dan kombinasi peran?
18. Ukuran kertas/printer dan dua salinan nota?
19. Surat Jalan untuk pengiriman saja atau juga pengambilan?
20. Laporan yang diperlukan: omzet, laba kotor, piutang, kasir, produk terlaris, stok?

Setiap keputusan APPROVED harus mencatat tanggal, pihak yang menyetujui, dan alasan. Jika berdampak pada skema atau alur, update PRD dan ARCHITECTURE sebelum coding.
