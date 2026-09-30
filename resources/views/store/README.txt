ZALINA FASHION - PDF RECEIPT

1. Copy receipt-pdf.blade.php ke:
resources/views/store/receipt-pdf.blade.php

2. Buka:
app/Http/Controllers/CheckoutController.php

Tambahkan import di bagian atas:
use Barryvdh\DomPDF\Facade\Pdf;

3. Replace HANYA method downloadReceipt lama dengan isi file:
CheckoutController-downloadReceipt.txt

4. Jalankan:
php artisan optimize:clear

5. Coba Download Receipt lagi.

Template PDF ini terpisah dari halaman website sehingga navbar, banner gratis ongkir,
footer website, tombol, dan icon yang berubah menjadi tanda tanya tidak akan ikut masuk PDF.
