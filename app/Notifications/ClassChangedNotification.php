<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassChangedNotification extends Notification
{
    use Queueable;

    protected $courseTitle;
    protected $oldClassName;
    protected $newClassName;

    public function __construct($courseTitle, $oldClassName, $newClassName)
    {
        $this->courseTitle = $courseTitle;
        $this->oldClassName = $oldClassName;
        $this->newClassName = $newClassName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Xác nhận đổi lớp: ' . $this->courseTitle)
                    ->greeting('Xin chào ' . ($notifiable->fullname ?? $notifiable->name ?? ''))
                    ->line('Yêu cầu đổi lớp cho khóa: ' . $this->courseTitle . ' đã được thực hiện.')
                    ->line('Lớp cũ: ' . ($this->oldClassName ?? '—'))
                    ->line('Lớp mới: ' . ($this->newClassName ?? '—'))
                    ->line('Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ quản trị hệ thống.')
                    ->salutation('Trân trọng, Khai Trí Edu');
    }
}
