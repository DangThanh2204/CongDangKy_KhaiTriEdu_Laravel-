<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RefundIssuedNotification extends Notification
{
    use Queueable;

    public $amount;
    public $courseTitle;
    public $className;

    public function __construct($amount, $courseTitle = null, $className = null)
    {
        $this->amount = $amount;
        $this->courseTitle = $courseTitle;
        $this->className = $className;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $amount = number_format($this->amount, 2);
        $subject = 'Hoàn tiền đăng ký khóa học';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Xin chào ' . ($notifiable->fullname ?? $notifiable->username ?? ''))
            ->line("Số tiền {$amount} đã được hoàn lại vào ví của bạn.");

        if ($this->courseTitle) {
            $mail->line('Khóa: ' . $this->courseTitle);
        }
        if ($this->className) {
            $mail->line('Lớp: ' . $this->className);
        }

        $mail->line('Số tiền sẽ được cập nhật vào số dư ví ngay lập tức.')
            ->action('Xem ví', url(route('wallet.index')))
            ->line('Cảm ơn bạn đã sử dụng hệ thống.');

        return $mail;
    }
}
