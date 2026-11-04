# Akun Demo

Seeder membuat satu akun untuk setiap role aplikasi.

Password awal semua akun:

```text
ChangeMe123!
```

| Role | Nama | Email |
|---|---|---|
| Admin | Admin Cake Store | `admin@cakestore.test` |
| Owner | Owner Cake Store | `owner@cakestore.test` |
| Kasir | Kasir Cake Store | `cashier@cakestore.test` |
| Sales | Sales Cake Store | `sales@cakestore.test` |
| Gudang | Gudang Cake Store | `warehouse@cakestore.test` |

Jalankan seeder:

```bash
php artisan db:seed --force
```

Docker:

```bash
docker compose exec app php artisan db:seed --force
```

Keamanan:

- Password hanya untuk development/UAT.
- Ganti password sebelum production.
- Jangan gunakan email `.test` untuk production.
- Jangan menyimpan password production di repository.
- Seeder memakai `updateOrCreate`, sehingga aman dijalankan ulang.
