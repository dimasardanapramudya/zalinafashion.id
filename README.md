# Zalina Fashion — Laravel

## Database XAMPP/phpMyAdmin
Buat database `db_zalina`, lalu set `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_zalina
DB_USERNAME=root
DB_PASSWORD=
```

## Jalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Customer dan admin login dari **Profile**. Tidak ada link admin di navbar publik.

Admin demo: `admin@zalina.local` / `Admin123!`
Customer demo: `aisyah@example.com` / `Customer123!`

## Payment
Admin mengatur rekening di `/admin/payment-methods`. Customer checkout, transfer ke rekening aktif, upload bukti, status menjadi `under_review`, lalu admin dapat konfirmasi atau menolak.

## Admin Settings & Homepage Slider

This build adds database-driven store configuration:

- Admin → Pengaturan Toko: store name, tagline, announcement bar, free-shipping threshold, slider speed, and logo upload.
- Admin → Slider & Iklan: create/edit/delete homepage advertising slides, upload slide images, choose CTA/link, sort order, active status, and one of five themes.
- Admin → Promo & Diskon: manage percent/fixed promotions and voucher codes.
- Product sale price remains available per product.
- Checkout accepts an active promo code and validates minimum order and active date.

After updating an existing database, run:

```bash
php artisan migrate
php artisan db:seed --class=SettingSeeder
php artisan db:seed --class=HomeSliderSeeder
php artisan storage:link
```

For a fresh XAMPP database (`db_zalina`):

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

## FINAL UI & ORDER UPDATE (September 2026)
- Professional SVG navigation, cart, profile, and mobile-menu icons (no emoji/sticker UI).
- Animated announcement ticker.
- Scroll reveal cards and subtle scroll bubbles.
- Premium footer with editable Instagram, Shopee, and WhatsApp links in Admin > Pengaturan Toko.
- Order history remains in Profile after checkout.
- Verified payments expose the payment receipt.
- Customer can confirm "Barang Sudah Sampai" after the admin marks an order as shipped.
- Admin can see delivery confirmation timestamp and update order status.

After replacing the project files, run:

```bash
php artisan migrate
php artisan optimize:clear
php artisan storage:link
php artisan serve
```
