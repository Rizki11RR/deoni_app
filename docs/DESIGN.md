# DESIGN — Prinsip UI/UX

## Tujuan
UI harus mudah dipahami staf toko, cepat untuk transaksi, dan mengurangi salah input.

## Prinsip
- Bahasa Indonesia untuk seluruh UI dan pesan validasi.
- Desktop/laptop sebagai target utama meja penjualan; responsif untuk tablet/HP.
- Tombol aksi utama jelas, konsisten, dan tidak berlebihan.
- Form order fokus pada pelanggan, tipe harga, produk, kuantitas, satuan, harga, diskon, catatan, dan ringkasan total.
- Tampilkan subtotal, diskon, pajak jika berlaku, pembulatan jika disetujui, total, pembayaran, dan sisa tagihan secara transparan.
- Status ditampilkan dengan teks dan warna, tidak mengandalkan warna saja.
- Konfirmasi untuk aksi berisiko: pembatalan, reversal pembayaran, koreksi stok.
- Order yang sudah dibayar read-only; koreksi melalui alur khusus.
- Pencarian order berdasarkan nomor, pelanggan, tanggal, dan status.

## Navigasi awal
Ringkasan, Penjualan/Order, Pembayaran, Produk, Pelanggan, Gudang, Pengiriman/Surat Jalan, Laporan, Pengaturan. Menu disesuaikan dengan peran; backend tetap memvalidasi akses.

## Cetak nota
Informasi: nomor nota, tanggal, pelanggan, item, kuantitas, satuan, harga, diskon, pajak bila berlaku, total, jumlah dibayar, sisa, dan status LUNAS/BELUM LUNAS. Sediakan salinan pelanggan dan arsip pemilik jika dikonfirmasi. Surat Jalan dokumen terpisah. Ukuran kertas dan printer harus diuji langsung.

## Operasional
Ukuran tombol memadai, format Rupiah konsisten, input angka tervalidasi, pesan error dekat field, dan jangan menampilkan kolom yang tidak relevan untuk peran pengguna.
