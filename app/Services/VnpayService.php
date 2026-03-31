<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VnpayService
{
    public function isConfigured(): bool
    {
        return filled(config('services.vnpay.tmn_code')) && filled(config('services.vnpay.hash_secret'));
    }

    public function buildPaymentUrl(Payment $payment, Request $request): string
    {
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => config('services.vnpay.tmn_code'),
            'vnp_Amount' => $this->normalizeAmount($payment),
            'vnp_CreateDate' => now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip() ?: '127.0.0.1',
            'vnp_Locale' => config('services.vnpay.locale', 'vn'),
            'vnp_OrderInfo' => $this->buildOrderInfo($payment),
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $this->returnUrl(),
            'vnp_TxnRef' => $payment->reference,
            'vnp_ExpireDate' => now('Asia/Ho_Chi_Minh')->addMinutes((int) config('services.vnpay.expire_minutes', 15))->format('YmdHis'),
        ];

        if ($bankCode = config('services.vnpay.bank_code')) {
            $params['vnp_BankCode'] = $bankCode;
        }

        ksort($params);

        $hashData = $this->buildQueryString($params);
        $secureHash = hash_hmac('sha512', $hashData, (string) config('services.vnpay.hash_secret'));

        return rtrim((string) config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'), '?')
            . '?' . $hashData . '&vnp_SecureHash=' . $secureHash;
    }

    public function verifyResponse(array $query): array
    {
        $payload = [];
        foreach ($query as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')) {
                $payload[$key] = is_array($value) ? reset($value) : $value;
            }
        }

        $receivedHash = $payload['vnp_SecureHash'] ?? '';
        unset($payload['vnp_SecureHash'], $payload['vnp_SecureHashType']);
        ksort($payload);

        $expectedHash = hash_hmac('sha512', $this->buildQueryString($payload), (string) config('services.vnpay.hash_secret'));

        return [
            'is_valid' => $receivedHash !== '' && hash_equals($expectedHash, $receivedHash),
            'payload' => $payload,
            'received_hash' => $receivedHash,
            'expected_hash' => $expectedHash,
        ];
    }

    public function isSuccessful(array $payload): bool
    {
        $responseCode = (string) ($payload['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($payload['vnp_TransactionStatus'] ?? '00');

        return $responseCode === '00' && $transactionStatus === '00';
    }

    public function amountMatches(Payment $payment, array $payload): bool
    {
        return (int) ($payload['vnp_Amount'] ?? 0) === $this->normalizeAmount($payment);
    }

    public function paymentReference(array $payload): ?string
    {
        $reference = $payload['vnp_TxnRef'] ?? null;

        return is_string($reference) && $reference !== '' ? $reference : null;
    }

    public function gatewaySummary(array $payload): string
    {
        $segments = ['VNPay'];

        if (! empty($payload['vnp_ResponseCode'])) {
            $segments[] = 'response=' . $payload['vnp_ResponseCode'];
        }

        if (! empty($payload['vnp_TransactionStatus'])) {
            $segments[] = 'status=' . $payload['vnp_TransactionStatus'];
        }

        if (! empty($payload['vnp_TransactionNo'])) {
            $segments[] = 'txn=' . $payload['vnp_TransactionNo'];
        }

        if (! empty($payload['vnp_BankCode'])) {
            $segments[] = 'bank=' . $payload['vnp_BankCode'];
        }

        if (! empty($payload['vnp_PayDate'])) {
            $segments[] = 'payDate=' . $payload['vnp_PayDate'];
        }

        return implode(' | ', $segments);
    }

    public function ipnUrl(): string
    {
        return $this->resolveUrl(config('services.vnpay.ipn_url'), 'payments.vnpay.ipn');
    }

    public function returnUrl(): string
    {
        return $this->resolveUrl(config('services.vnpay.return_url'), 'payments.vnpay.return');
    }

    private function resolveUrl(?string $configuredUrl, string $routeName): string
    {
        return filled($configuredUrl) ? (string) $configuredUrl : route($routeName);
    }

    private function normalizeAmount(Payment $payment): int
    {
        return (int) round(((float) $payment->amount) * 100);
    }

    private function buildOrderInfo(Payment $payment): string
    {
        $payment->loadMissing('courseClass.course');

        $courseTitle = $payment->courseClass?->course?->title ?: 'Khai Tri Edu';
        $raw = 'Thanh toan hoc phi ' . $courseTitle . ' ma ' . $payment->reference;
        $ascii = Str::ascii($raw);
        $normalized = preg_replace('/[^A-Za-z0-9\s]/', ' ', $ascii) ?: 'Thanh toan hoc phi';

        return trim(preg_replace('/\s+/', ' ', $normalized) ?: 'Thanh toan hoc phi');
    }

    private function buildQueryString(array $params): string
    {
        $segments = [];

        foreach ($params as $key => $value) {
            $segments[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        return implode('&', $segments);
    }
}