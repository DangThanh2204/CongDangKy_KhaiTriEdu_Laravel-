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
            $issues[] = 'Thiếu VNPAY_URL.';
        }

        if (! filled(config('services.vnpay.tmn_code'))) {
            $issues[] = 'Thiếu VNPAY_TMN_CODE.';
        }

        if (! filled(config('services.vnpay.hash_secret'))) {
            $issues[] = 'Thiếu VNPAY_HASH_SECRET.';
        }

        if (! filled($this->returnUrl())) {
            $issues[] = 'Thiếu VNPAY_RETURN_URL.';
        }

        if (! filled($this->ipnUrl())) {
            $issues[] = 'Thiếu VNPAY_IPN_URL.';
        }

        if (app()->environment('production') && $this->isSandbox() && ! $this->allowsSandboxOnProduction()) {
            $issues[] = 'Môi trường production hiện vẫn đang dùng URL sandbox của VNPay.';
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
            reference: $this->sanitizeReference((string) $payment->reference, 'Mã phiếu thanh toán không hợp lệ để gửi sang VNPay.'),
            amountValue: (float) $payment->amount,
            orderInfo: $this->buildPaymentOrderInfo($payment),
            request: $request,
        );
    }

    public function buildWalletTopupUrl(WalletTransaction $walletTransaction, Request $request): string
    {
        return $this->buildGatewayUrl(
            reference: $this->sanitizeReference((string) $walletTransaction->reference, 'Mã giao dịch nạp ví không hợp lệ để gửi sang VNPay.'),
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
            '00' => 'Giao dịch thành công.',
            '07' => 'Giao dịch bị nghi ngờ gian lận hoặc bất thường.',
            '09' => 'Tài khoản hoặc thẻ chưa đăng ký Internet Banking.',
            '10' => 'Thông tin xác thực tài khoản hoặc thẻ không đúng quá 3 lần.',
            '11' => 'Giao dịch đã hết hạn chờ thanh toán.',
            '12' => 'Tài khoản hoặc thẻ đã bị khóa.',
            '13' => 'Mật khẩu xác thực hoặc OTP không đúng.',
            '24' => 'Khách hàng đã hủy giao dịch.',
            '51' => 'Tài khoản không đủ số dư để thanh toán.',
            '65' => 'Tài khoản đã vượt hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Khách hàng nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Giao dịch gặp lỗi chưa xác định từ VNPay.',
        ];

        $transactionMessages = [
            '00' => 'Trạng thái giao dịch: thành công.',
            '01' => 'Trạng thái giao dịch: chưa hoàn tất.',
            '02' => 'Trạng thái giao dịch: bị lỗi.',
            '04' => 'Trạng thái giao dịch: giao dịch đảo.',
            '05' => 'Trạng thái giao dịch: VNPay đang xử lý hoàn tiền.',
            '06' => 'Trạng thái giao dịch: VNPay đã gửi yêu cầu hoàn tiền sang ngân hàng.',
            '07' => 'Trạng thái giao dịch: nghi ngờ gian lận.',
            '09' => 'Trạng thái giao dịch: hoàn trả bị từ chối.',
        ];

        $segments = [];

        if ($responseCode !== '' && isset($responseMessages[$responseCode])) {
            $segments[] = $responseMessages[$responseCode];
        }

        if ($transactionStatus !== '' && isset($transactionMessages[$transactionStatus]) && $transactionStatus !== '00') {
            $segments[] = $transactionMessages[$transactionStatus];
        }

        if ($segments === []) {
            return 'VNPay đã phản hồi nhưng chưa có mô tả lỗi cụ thể.';
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
            throw new RuntimeException('Số tiền thanh toán VNPay phải lớn hơn 0.');
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
