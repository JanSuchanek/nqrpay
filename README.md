# NQrPay — Czech QR Platba (Short Payment Descriptor)

QR code generator for Czech/Slovak mobile banking apps. Generates SPD (Short Payment Descriptor) strings and QR codes.

## Installation

```bash
composer require jansuchanek/nqrpay
```

## Usage

```php
use NQrPay\QrPayment;

$qr = new QrPayment(
    iban: 'CZ6508000000192000145399',
    accountName: 'Jan Suchánek',
    bankAccountNumber: '192000145399/0800',
);

// Generate SPD string
$spd = $qr->generateSpdString(1250.50, '2024001', 'Objednavka 001');
// SPD*1.0*ACC:CZ6508000000192000145399*AM:1250.50*CC:CZK*X-VS:2024001*MSG:Objednavka 001

// Generate QR code PNG
$png = $qr->generateQrPng(1250.50, '2024001');

// Save QR to file
$qr->generateQrFile('/path/to/qr', 'pay-001.png', 1250.50, '2024001');

// Extract variable symbol from order number
$vs = QrPayment::extractVariableSymbol('OBJ-20260303-1234');
// '202603031234'

// Account details for display
$details = $qr->getAccountDetails();
// ['ibanFormatted' => 'CZ65 0800 0000 1920 0014 5399', ...]
```

## Requirements

- PHP >= 8.1
- endroid/qr-code ^5.0|^6.0
