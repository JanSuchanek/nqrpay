<?php
declare(strict_types=1);
namespace NQrPay\Tests;
use NQrPay\QrPayment;
use Tester\Assert;
use Tester\TestCase;
require __DIR__ . '/../vendor/autoload.php';
\Tester\Environment::setup();

class QrPaymentTest extends TestCase
{
	public function testSpdStringFormat(): void
	{
		$qr = new QrPayment('CZ6508000000192000145399');
		$spd = $qr->generateSpdString(1250.50, '2024001', 'Objednavka 001');

		Assert::contains('SPD*1.0', $spd);
		Assert::contains('ACC:CZ6508000000192000145399', $spd);
		Assert::contains('AM:1250.50', $spd);
		Assert::contains('CC:CZK', $spd);
		Assert::contains('X-VS:2024001', $spd);
		Assert::contains('MSG:Objednavka 001', $spd);
	}

	public function testSpdWithoutMessage(): void
	{
		$qr = new QrPayment('CZ6508000000192000145399');
		$spd = $qr->generateSpdString(100.00, '123');
		Assert::notContains('MSG:', $spd);
	}

	public function testCustomCurrency(): void
	{
		$qr = new QrPayment('SK3112000000198742637541', currency: 'EUR');
		$spd = $qr->generateSpdString(50.00, '456');
		Assert::contains('CC:EUR', $spd);
	}

	public function testExtractVariableSymbol(): void
	{
		Assert::same('202603031234', QrPayment::extractVariableSymbol('OBJ-20260303-1234'));
		Assert::same('12345', QrPayment::extractVariableSymbol('12345'));
		Assert::same('', QrPayment::extractVariableSymbol('abc'));
	}

	public function testIsConfigured(): void
	{
		Assert::true((new QrPayment('CZ123'))->isConfigured());
		Assert::false((new QrPayment(''))->isConfigured());
	}

	public function testAccountDetails(): void
	{
		$qr = new QrPayment('CZ6508000000192000145399', 'Jan Suchanek', '192000145399/0800');
		$details = $qr->getAccountDetails();
		Assert::same('CZ65 0800 0000 1920 0014 5399', $details['ibanFormatted']);
		Assert::same('Jan Suchanek', $details['accountName']);
	}

	public function testFormatIbanGroupsOf4(): void
	{
		$qr = new QrPayment('CZ6508000000192000145399');
		$d = $qr->getAccountDetails();
		Assert::same('CZ65 0800 0000 1920 0014 5399', $d['ibanFormatted']);
	}
}
(new QrPaymentTest())->run();
