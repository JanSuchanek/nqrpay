<?php

declare(strict_types=1);

namespace NQrPay;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Czech QR Platba — SPD (Short Payment Descriptor) generator.
 *
 * Generates QR codes scannable by Czech/Slovak mobile banking apps.
 *
 * @see https://qr-platba.cz
 */
class QrPayment
{
	public function __construct(
		private readonly string $iban,
		private readonly string $accountName = '',
		private readonly string $bankAccountNumber = '',
		private readonly string $currency = 'CZK',
	) {}


	/**
	 * Generate SPD string for a payment.
	 *
	 * @param float $amount Payment amount
	 * @param string $variableSymbol Variable symbol (VS)
	 * @param string $message Payment message/description
	 */
	public function generateSpdString(float $amount, string $variableSymbol, string $message = ''): string
	{
		$parts = [
			'SPD*1.0',
			'ACC:' . $this->iban,
			'AM:' . number_format($amount, 2, '.', ''),
			'CC:' . $this->currency,
			'X-VS:' . $variableSymbol,
		];

		if ($message !== '') {
			$parts[] = 'MSG:' . mb_substr($message, 0, 60);
		}

		return implode('*', $parts);
	}


	/**
	 * Generate QR code as PNG binary string.
	 */
	public function generateQrPng(float $amount, string $variableSymbol, string $message = '', int $size = 300): string
	{
		$spd = $this->generateSpdString($amount, $variableSymbol, $message);

		$result = (new Builder(
			writer: new PngWriter(),
			data: $spd,
			encoding: new Encoding('UTF-8'),
			errorCorrectionLevel: ErrorCorrectionLevel::Medium,
			size: $size,
			margin: 10,
		))->build();

		return $result->getString();
	}


	/**
	 * Generate QR code and save to file, return relative path.
	 */
	public function generateQrFile(
		string $outputDir,
		string $filename,
		float $amount,
		string $variableSymbol,
		string $message = '',
	): string {
		if (!is_dir($outputDir)) {
			mkdir($outputDir, 0755, true);
		}

		$png = $this->generateQrPng($amount, $variableSymbol, $message);
		file_put_contents($outputDir . '/' . $filename, $png);

		return $filename;
	}


	/**
	 * Get payment details for display.
	 *
	 * @return array{iban: string, ibanFormatted: string, accountNumber: string, accountName: string, currency: string}
	 */
	public function getAccountDetails(): array
	{
		return [
			'iban' => $this->iban,
			'ibanFormatted' => $this->formatIban($this->iban),
			'accountNumber' => $this->bankAccountNumber,
			'accountName' => $this->accountName,
			'currency' => $this->currency,
		];
	}


	/**
	 * Extract numeric variable symbol from a string (e.g. order number).
	 * OBJ-20260303-1234 → 202603031234
	 */
	public static function extractVariableSymbol(string $orderNumber): string
	{
		return preg_replace('/[^0-9]/', '', $orderNumber);
	}


	public function isConfigured(): bool
	{
		return $this->iban !== '';
	}


	private function formatIban(string $iban): string
	{
		return trim(chunk_split(str_replace(' ', '', $iban), 4, ' '));
	}
}
