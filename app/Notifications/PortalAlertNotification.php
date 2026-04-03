<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected array $payload,
        protected array $channels = ['database', 'mail'],
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject((string) ($this->payload['subject'] ?? $this->payload['title'] ?? 'Thông báo từ Khai Trí Edu'))
            ->greeting('Xin chào ' . ($notifiable->fullname ?? $notifiable->username ?? 'bạn'))
            ->line((string) ($this->payload['message'] ?? 'Bạn có một thông báo mới từ hệ thống.'));

        foreach ((array) ($this->payload['lines'] ?? []) as $line) {
            if (filled($line)) {
                $mail->line((string) $line);
            }
        }

        if (filled($this->payload['action_url']) && filled($this->payload['action_text'])) {
            $mail->action((string) $this->payload['action_text'], (string) $this->payload['action_url']);
        }

        return $mail->line('Trung tâm Khai Trí sẽ tiếp tục cập nhật cho bạn ngay khi có thay đổi mới.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->payload['category'] ?? 'general',
            'title' => $this->payload['title'] ?? 'Thông báo mới',
            'message' => $this->payload['message'] ?? '',
            'action_url' => $this->payload['action_url'] ?? null,
            'action_text' => $this->payload['action_text'] ?? null,
            'icon' => $this->payload['icon'] ?? 'fas fa-bell',
            'variant' => $this->payload['variant'] ?? 'primary',
            'entity_type' => $this->payload['entity_type'] ?? null,
            'entity_id' => $this->payload['entity_id'] ?? null,
            'reminder_key' => $this->payload['reminder_key'] ?? null,
            'subject' => $this->payload['subject'] ?? null,
        ];
    }
}