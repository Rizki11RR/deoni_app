# PRD — Sistem Manajemen Toko Bahan Kue

## Ringkasan
Aplikasi web internal untuk mengelola produk, pelanggan, order/nota, pembayaran, persiapan gudang, stok, pengambilan/pengiriman, dan laporan. Dibangun mandiri, tidak wajib bergantung pada Odoo.

## Konteks operasional yang diketahui
Alur lama yang pernah dijelaskan:
1. Pesanan masuk melalui WhatsApp Admin.
2. Staf depan menyiapkan nota penjualan dan catatan gudang.
3. Kasir memproses pembayaran.
4. Gudang menyiapkan barang.
5. Barang diambil pelanggan atau dikirim.
6. Untuk pelanggan tertentu, transaksi dapat berupa bon/belum lunas; salinan nota dapat ditahan kasir.
7. Nota lunas diberi penanda lunas dan diarsipkan.
Pembayaran dapat dilakukan penuh atau sebagian, memakai satu atau beberapa metode. Barang boleh diambil atau dikirim sebelum lunas. Pengiriman parsial tidak diperbolehkan.

## Tujuan
- Menggantikan proses nota manual berlembar banyak secara bertahap.
- Menyatukan informasi order, pembayaran, gudang, dan pengiriman.
- Mendukung harga Toko dan Hotel.
- Mengurangi kesalahan input dan menyediakan jejak audit.
- Menyediakan UI Bahasa Indonesia yang mudah digunakan staf.

## Pengguna/peran konseptual
- Admin/Pengelola: konfigurasi, master data, laporan, koreksi terbatas.
- Staff Penjualan: membuat order/nota.
- Kasir: mencatat pembayaran dan melihat saldo tagihan.
- Gudang: melihat daftar barang dan memperbarui status persiapan.
- Pengiriman: mengelola surat jalan dan status kirim.
- Pemilik: laporan dan audit.
Satu orang dapat memegang beberapa peran. Akses lintas staf perlu tetap mencatat siapa pelaku setiap aksi.

## Ruang lingkup MVP yang disarankan
1. Login, pengguna, role dan permission.
2. Master produk, kategori, satuan, SKU opsional.
3. Master pelanggan dan tipe harga.
4. Harga Toko dan Hotel.
5. Pembuatan order/nota dan item.
6. Status order serta riwayat aktivitas.
7. Pembayaran penuh/parsial (parsial perlu persetujuan).
8. Cetak nota pelanggan dan arsip toko.
9. Daftar persiapan gudang.
10. Pengambilan/pengiriman dan Surat Jalan.
11. Laporan penjualan dan pembayaran dasar.
12. Backup dan prosedur restore.

## Kebutuhan awal
### Produk dan satuan
Produk mempunyai nama, kode opsional, kategori, satuan jual, harga Toko, harga Hotel, dan status aktif. Satuan dapat berupa carton, pack, pcs, roll, dan lainnya. Konversi satuan hanya dibuat bila faktor konversi diketahui dan disetujui.

### Harga
Harga Toko dan Hotel dipisahkan. Harga Hotel selalu 10% lebih tinggi dari harga Toko. Staff boleh mengubah harga pada transaksi; harga master dan harga transaksi tetap menjadi snapshot, serta perubahan harga dicatat dalam audit.

### Order dan nota
Nota besar terdiri dari Nota Toko, Nota Hotel, dan Nota Sales. Nota kecil/POS boleh tanpa pelanggan. Simpan pelanggan bila ada, tipe harga/dokumen, item, kuantitas, satuan, harga snapshot, perubahan harga, diskon, pajak 11% termasuk harga, total, catatan, pembuat, waktu, dan status. Setiap jenis nota dan Surat Jalan memakai sequence angka terpisah yang melanjutkan nomor lama. Nomor harus aman dari duplikasi. Nota besar dicetak dua salinan: pelanggan dan arsip pemilik. Nota internal bukan otomatis faktur pajak.

### Pembayaran
Simpan nominal, metode, waktu, kasir, referensi, dan catatan. Status lunas/belum lunas dihitung dari saldo. Pembayaran yang sudah dicatat tidak dihapus; koreksi melalui reversal dengan alasan dan audit. Pembayaran parsial/bon diperbolehkan. Semua kasir boleh menyetujui pembayaran parsial/bon.

### Gudang dan stok
Stok dimigrasikan bertahap karena data awal belum lengkap dan perlu dihitung ulang. Tandai stok belum diverifikasi. Sistem membedakan stok fisik, stok tercadangkan, dan stok tersedia. Order mencadangkan stok; barang keluar atau diserahkan mengurangi stok fisik; pembatalan order melepas cadangan. Order dengan stok kurang tetap boleh dibuat dengan peringatan. Semua perubahan stok melalui stock movement/adjustment dengan alasan dan pelaku; jangan edit stok historis langsung di database.

### Pengiriman/pengambilan
Bedakan ambil sendiri dan dikirim. Surat Jalan merupakan dokumen tersendiri, opsional, hanya untuk pengiriman. Pengiriman parsial tidak diperbolehkan. Tanda tangan penerima tetap dilakukan di kertas dan tidak wajib disimpan di sistem.

### Pajak, diskon, pembulatan
Pajak berlaku untuk semua produk dengan tarif 11% dan harga sudah termasuk pajak. Diskon nominal dan persentase tersedia tanpa batas; semua staff boleh memberi diskon. Tidak ada pembulatan Rp500.

## Non-functional
Bahasa Indonesia, IDR, timezone Asia/Jakarta, target awal desktop dengan responsif tablet/HP, server-side authorization, audit trail, backup teruji, konsistensi saat beberapa staf bekerja bersamaan. Perkiraan pengguna awal 1–4.

## Di luar MVP
Integrasi WhatsApp API, akuntansi penuh, faktur pajak elektronik, e-commerce publik, multi-cabang kompleks, aplikasi mobile native, integrasi marketplace, payroll.

Lihat `DECISIONS.md` untuk keputusan bisnis yang telah disetujui dan batasan yang berlaku.
