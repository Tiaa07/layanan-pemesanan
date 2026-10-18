# Layanan Pemesanan Produk

Layanan Pemesanan Produk merupakan REST API berbasis Laravel yang digunakan untuk membuat, melihat, mengonfirmasi, dan membatalkan pesanan produk.

API menggunakan versi `v1` dan menyediakan response error dalam format `application/problem+json`.

## Fitur

Layanan menyediakan empat operasi utama:

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/api/v1/pesanan` | Membuat pesanan baru |
| GET | `/api/v1/pesanan/{kode}` | Melihat detail pesanan |
| POST | `/api/v1/pesanan/{kode}/konfirmasi` | Mengonfirmasi pesanan |
| POST | `/api/v1/pesanan/{kode}/batal` | Membatalkan pesanan |

Status pesanan terdiri dari:

- `menunggu`
- `dikonfirmasi`
- `dibatalkan`

Pesanan baru selalu memiliki status `menunggu`.

Pesanan hanya dapat dikonfirmasi atau dibatalkan ketika masih berstatus `menunggu`. Apabila operasi bertentangan dengan status pesanan, API mengembalikan `409 Conflict`.

## Menjalankan Project

Pastikan Docker Desktop dan WSL sudah berjalan.

Jalankan container Laravel Sail:

```bash
./vendor/bin/sail up -d
```

Aplikasi dapat diakses melalui:

```text
http://localhost:8082
```

## Migration Database

Jalankan migration dengan:

```bash
./vendor/bin/sail artisan migrate
```

## Menjalankan Test

Seluruh automated test dapat dijalankan dengan:

```bash
./vendor/bin/sail artisan test
```

Hasil pengujian terakhir:

```text
Tests: 10 passed (59 assertions)
```

Khusus pengujian API pemesanan:

```bash
./vendor/bin/sail artisan test --filter=PesananApiTest
```

API pemesanan memiliki delapan skenario pengujian:

1. Berhasil membuat pesanan baru (`201 Created`)
2. Gagal membuat pesanan dengan data tidak valid (`422 Unprocessable Content`)
3. Berhasil melihat detail pesanan (`200 OK`)
4. Pesanan tidak ditemukan (`404 Not Found`)
5. Berhasil mengonfirmasi pesanan (`200 OK`)
6. Gagal mengonfirmasi pesanan yang sudah dibatalkan (`409 Conflict`)
7. Berhasil membatalkan pesanan (`200 OK`)
8. Gagal membatalkan pesanan yang sudah dikonfirmasi (`409 Conflict`)

## OpenAPI

Kontrak REST API tersedia pada:

```text
openapi.yaml
```

Validasi kontrak OpenAPI menggunakan Spectral:

```bash
docker run --rm \
-v "$(pwd)":/work \
-w /work \
stoplight/spectral lint openapi.yaml
```

Kontrak telah diperiksa dan menghasilkan:

```text
No results with a severity of 'error' found!
```

## Swagger UI

Swagger UI dapat dijalankan menggunakan Docker:

```bash
docker run --rm -d \
--name layanan-pemesanan-swagger \
-p 8083:8080 \
-e SWAGGER_JSON=/spec/openapi.yaml \
-v "$(pwd)":/spec \
swaggerapi/swagger-ui
```

Setelah container berjalan, dokumentasi Swagger dapat dibuka melalui:

```text
http://localhost:8083
```

## Contoh Membuat Pesanan

Request:

```http
POST /api/v1/pesanan
Content-Type: application/json
```

Body:

```json
{
  "nama_pelanggan": "Tia Anggraeni",
  "sku_produk": "KRD-001",
  "jumlah": 2
}
```

Contoh response:

```json
{
  "data": {
    "kode_pesanan": "PSN-XXXXXXXX",
    "nama_pelanggan": "Tia Anggraeni",
    "sku_produk": "KRD-001",
    "jumlah": 2,
    "status": "menunggu"
  }
}
```

## Problem Details

Error API dikembalikan menggunakan media type:

```text
application/problem+json
```

Contoh `409 Conflict`:

```json
{
  "type": "https://layanan-pemesanan.test/problem/konflik-status",
  "title": "Status pesanan tidak sesuai",
  "status": 409,
  "detail": "Pesanan yang tidak berstatus menunggu tidak dapat dibatalkan.",
  "instance": "/api/v1/pesanan/PSN-XXXXXXXX/batal"
}
```

## Teknologi

- Laravel
- Laravel Sail
- MySQL
- PHPUnit
- OpenAPI 3.1
- Spectral
- Swagger UI
- Docker
