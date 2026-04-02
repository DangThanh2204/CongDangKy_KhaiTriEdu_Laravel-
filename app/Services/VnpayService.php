<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class VnpayService
{
    public function isConfigured(): bool
    {
        return $this->configurationIssues() === [];
    }

    public function configurationIssues(): array
    {
        $issues = [];

        if (! filled($this->gatewayUrl())) {
            $issues[] = 'ThiÃ¡ÂºÂ¿u VNPAY_URL.';
        }

        if (! filled(config('services.vnpay.tmn_code'))) {
            $issues[] = 'ThiÃ¡ÂºÂ¿u VNPAY_TMN_CODE.';
        }

        if (! filled(config('services.vnpay.hash_secret'))) {
            $issues[] = 'ThiÃ¡ÂºÂ¿u VNPAY_HASH_SECRET.';
        }

        if (! filled($this->returnUrl())) {
            $issues[] = 'ThiÃ¡ÂºÂ¿u VNPAY_RETURN_URL.';
        }

        if (! filled($this->ipnUrl())) {
            $issues[] = 'ThiÃ¡ÂºÂ¿u VNPAY_IPN_URL.';
        }

        if (app()->environment('production') && $this->isSandbox() && ! $this->allowsSandboxOnProduction()) {
            $issues[] = 'MÃ´i trÆ°á»ng production hiá»‡n váº«n Ä‘ang dÃ¹ng URL sandbox cá»§a VNPay.';
        }

        return array_values(array_unique($issues));
    }

    public function configurationSummary(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'issues' => $this->configurationIssues(),
            'gateway_url' => $this->gatewayUrl(),
            'return_url' => $this->returnUrl(),
            'ipn_url' => $this->ipnUrl(),
            'environment' => $this->isSandbox() ? 'sandbox' : 'production',
            'environment_label' => $this->isSandbox() ? 'Sandbox' : 'Production',
        ];
    }

    public function isSandbox(): bool
    {
        return str_contains(strtolower($this->gatewayUrl()), 'sandbox.vnpayment.vn');
    }

    public function allowsSandboxOnProduction(): bool
    {
        return (bool) config('services.vnpay.allow_sandbox_on_production', false);
    }

    public function buildPaymentUrl(Payment $payment, Request $request): string
    {
        return $this->buildGatewayUrl(
            reference: $this->sanitizeReference((string) $payment->reference, 'MÃƒÂ£ phiÃ¡ÂºÂ¿u thanh toÃƒÂ¡n khÃƒÂ´ng hÃ¡Â»Â£p lÃ¡Â»â€¡ Ã„â€˜Ã¡Â»Æ’ gÃ¡Â»Â­i sang VNPay.'),
            amountValue: (float) $payment->amount,
            orderInfo: $this->buildPaymentOrderInfo($payment),
            request: $request,
        );
    }

    public function buildWalletTopupUrl(WalletTransaction $walletTransaction, Request $request): string
    {
        return $this->buildGatewayUrl(
            reference: $this->sanitizeReference((string) $walletTransaction->reference, 'MÃƒÂ£ giao dÃ¡Â»â€¹ch nÃ¡ÂºÂ¡p vÃƒÂ­ khÃƒÂ´ng hÃ¡Â»Â£p lÃ¡Â»â€¡ Ã„â€˜Ã¡Â»Æ’ gÃ¡Â»Â­i sang VNPay.'),
            amountValue: (float) $walletTransaction->amount,
            orderInfo: $this->buildWalletTopupOrderInfo($walletTransaction),
            request: $request,
        );
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

    public function responseMessage(array $payload): string
    {
        $responseCode = (string) ($payload['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($payload['vnp_TransactionStatus'] ?? '');

        $responseMessages = [
            '00' => 'Giao dÃ¡Â»â€¹ch thÃƒÂ nh cÃƒÂ´ng.',
            '07' => 'Giao dÃ¡Â»â€¹ch bÃ¡Â»â€¹ nghi ngÃ¡Â»Â gian lÃ¡ÂºÂ­n hoÃ¡ÂºÂ·c bÃ¡ÂºÂ¥t thÃ†Â°Ã¡Â»Âng.',
            '09' => 'TÃƒÂ i khoÃ¡ÂºÂ£n hoÃ¡ÂºÂ·c thÃ¡ÂºÂ» chÃ†Â°a Ã„â€˜Ã„Æ’ng kÃƒÂ½ Internet Banking.',
            '10' => 'ThÃƒÂ´ng tin xÃƒÂ¡c thÃ¡Â»Â±c tÃƒÂ i khoÃ¡ÂºÂ£n hoÃ¡ÂºÂ·c thÃ¡ÂºÂ» khÃƒÂ´ng Ã„â€˜ÃƒÂºng quÃƒÂ¡ 3 lÃ¡ÂºÂ§n.',
            '11' => 'Giao dÃ¡Â»â€¹ch Ã„â€˜ÃƒÂ£ hÃ¡ÂºÂ¿t hÃ¡ÂºÂ¡n chÃ¡Â»Â thanh toÃƒÂ¡n.',
            '12' => 'TÃƒÂ i khoÃ¡ÂºÂ£n hoÃ¡ÂºÂ·c thÃ¡ÂºÂ» Ã„â€˜ÃƒÂ£ bÃ¡Â»â€¹ khÃƒÂ³a.',
            '13' => 'MÃ¡ÂºÂ­t khÃ¡ÂºÂ©u xÃƒÂ¡c thÃ¡Â»Â±c hoÃ¡ÂºÂ·c OTP khÃƒÂ´ng Ã„â€˜ÃƒÂºng.',
            '24' => 'KhÃƒÂ¡ch hÃƒÂ ng Ã„â€˜ÃƒÂ£ hÃ¡Â»Â§y giao dÃ¡Â»â€¹ch.',
            '51' => 'TÃƒÂ i khoÃ¡ÂºÂ£n khÃƒÂ´ng Ã„â€˜Ã¡Â»Â§ sÃ¡Â»â€˜ dÃ†Â° Ã„â€˜Ã¡Â»Æ’ thanh toÃƒÂ¡n.',
            '65' => 'TÃƒÂ i khoÃ¡ÂºÂ£n Ã„â€˜ÃƒÂ£ vÃ†Â°Ã¡Â»Â£t hÃ¡ÂºÂ¡n mÃ¡Â»Â©c giao dÃ¡Â»â€¹ch trong ngÃƒÂ y.',
            '75' => 'NgÃƒÂ¢n hÃƒÂ ng thanh toÃƒÂ¡n Ã„â€˜ang bÃ¡ÂºÂ£o trÃƒÂ¬.',
            '79' => 'KhÃƒÂ¡ch hÃƒÂ ng nhÃ¡ÂºÂ­p sai mÃ¡ÂºÂ­t khÃ¡ÂºÂ©u thanh toÃƒÂ¡n quÃƒÂ¡ sÃ¡Â»â€˜ lÃ¡ÂºÂ§n quy Ã„â€˜Ã¡Â»â€¹nh.',
            '99' => 'Giao dÃ¡Â»â€¹ch gÃ¡ÂºÂ·p lÃ¡Â»â€”i chÃ†Â°a xÃƒÂ¡c Ã„â€˜Ã¡Â»â€¹nh tÃ¡Â»Â« VNPay.',
        ];

        $transactionMessages = [
            '00' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: thÃƒÂ nh cÃƒÂ´ng.',
            '01' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: chÃ†Â°a hoÃƒÂ n tÃ¡ÂºÂ¥t.',
            '02' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: bÃ¡Â»â€¹ lÃ¡Â»â€”i.',
            '04' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: giao dÃ¡Â»â€¹ch Ã„â€˜Ã¡ÂºÂ£o.',
            '05' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: VNPay Ã„â€˜ang xÃ¡Â»Â­ lÃƒÂ½ hoÃƒÂ n tiÃ¡Â»Ân.',
            '06' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: VNPay Ã„â€˜ÃƒÂ£ gÃ¡Â»Â­i yÃƒÂªu cÃ¡ÂºÂ§u hoÃƒÂ n tiÃ¡Â»Ân sang ngÃƒÂ¢n hÃƒÂ ng.',
            '07' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: nghi ngÃ¡Â»Â gian lÃ¡ÂºÂ­n.',
            '09' => 'TrÃ¡ÂºÂ¡ng thÃƒÂ¡i giao dÃ¡Â»â€¹ch: hoÃƒÂ n trÃ¡ÂºÂ£ bÃ¡Â»â€¹ tÃ¡Â»Â« chÃ¡Â»â€˜i.',
        ];

        $segments = [];

        if ($responseCode !== '' && isset($responseMessages[$responseCode])) {
            $segments[] = $responseMessages[$responseCode];
        }

        if ($transactionStatus !== '' && isset($transactionMessages[$transactionStatus]) && $transactionStatus !== '00') {
            $segments[] = $transactionMessages[$transactionStatus];
        }

        if ($segments === []) {
            return 'VNPay Ã„â€˜ÃƒÂ£ phÃ¡ÂºÂ£n hÃ¡Â»â€œi nhÃ†Â°ng chÃ†Â°a cÃƒÂ³ mÃƒÂ´ tÃ¡ÂºÂ£ lÃ¡Â»â€”i cÃ¡Â»Â¥ thÃ¡Â»Æ’.';
        }

        return implode(' ', $segments);
    }

    public function amountMatches(Payment $payment, array $payload): bool
    {
        return $this->amountMatchesValue((float) $payment->amount, $payload);
    }

    public function amountMatchesValue(float|int|string $amount, array $payload): bool
    {
        return (int) ($payload['vnp_Amount'] ?? 0) === $this->normalizeAmountValue((float) $amount);
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

    public function gatewayUrl(): string
    {
        return trim((string) config('services.vnpay.url', ''));
    }

    private function resolveUrl(?string $configuredUrl, string $routeName): string
    {
        return filled($configuredUrl) ? trim((string) $configuredUrl) : route($routeName);
    }

    private function buildGatewayUrl(string $reference, float $amountValue, string $orderInfo, Request $request): string
    {
        $issues = $this->configurationIssues();
        if ($issues !== []) {
            throw new RuntimeException(implode(' ', $issues));
        }

        $amount = $this->normalizeAmountValue($amountValue);
        if ($amount <= 0) {
            throw new RuntimeException('SÃ¡Â»â€˜ tiÃ¡Â»Ân thanh toÃƒÂ¡n VNPay phÃ¡ÂºÂ£i lÃ¡Â»â€ºn hÃ†Â¡n 0.');
        }

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => (string) config('services.vnpay.tmn_code'),
            'vnp_Amount' => $amount,
            'vnp_CreateDate' => now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip() ?: '127.0.0.1',
            'vnp_Locale' => (string) config('services.vnpay.locale', 'vn'),
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $this->returnUrl(),
            'vnp_TxnRef' => $reference,
            'vnp_ExpireDate' => now('Asia/Ho_Chi_Minh')
                ->addMinutes((int) config('services.vnpay.expire_minutes', 15))
                ->format('YmdHis'),
        ];

        if ($bankCode = config('services.vnpay.bank_code')) {
            $params['vnp_BankCode'] = $bankCode;
        }

        ksort($params);

        $hashData = $this->buildQueryString($params);
        $secureHash = hash_hmac('sha512', $hashData, (string) config('services.vnpay.hash_secret'));

        return rtrim($this->gatewayUrl(), '?')
            . '?' . $hashData . '&vnp_SecureHash=' . $secureHash;
    }

    private function normalizeAmountValue(float $amount): int
    {
        return (int) round(max($amount, 0) * 100);
    }

    private function sanitizeReference(string $reference, string $errorMessage): string
    {
        $reference = preg_replace('/[^A-Za-z0-9_-]/', '', $reference) ?: '';
        $reference = substr($reference, 0, 100);

        if ($reference === '') {
            throw new RuntimeException($errorMessage);
        }

        return $reference;
    }

    private function buildPaymentOrderInfo(Payment $payment): string
    {
        $payment->loadMissing('courseClass.course');

        $courseTitle = $payment->courseClass?->course?->title ?: 'Khai Tri Edu';

        return $this->normalizeOrderInfo('Thanh toan hoc phi ' . $courseTitle . ' ma ' . $payment->reference);
    }

    private function buildWalletTopupOrderInfo(WalletTransaction $walletTransaction): string
    {
        return $this->normalizeOrderInfo('Nap vi hoc tap Khai Tri ma ' . $walletTransaction->reference);
    }

    private function normalizeOrderInfo(string $raw): string
    {
        $ascii = Str::ascii($raw);
        $normalized = preg_replace('/[^A-Za-z0-9\s]/', ' ', $ascii) ?: 'Thanh toan VNPAY';
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?: 'Thanh toan VNPAY');

        return substr($normalized, 0, 255);
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
