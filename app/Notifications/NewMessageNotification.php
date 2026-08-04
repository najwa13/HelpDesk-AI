<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->message->ticket_id,
            'message_id' => $this->message->id,
            'message' => "Une nouvelle réponse a été ajoutée au ticket #{$this->message->ticket_id}.",
        ];
    }
}
