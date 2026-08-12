<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AgentNewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $ref = '#TK-'.str_pad((string) $this->message->ticket_id, 4, '0', STR_PAD_LEFT);
        $senderName = $this->message->auteur?->name ?? 'Client';

        return [
            'type' => 'new_message',
            'ticket_id' => $this->message->ticket_id,
            'ticket_ref' => $ref,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->auteur_id,
            'sender_name' => $senderName,
            'title' => 'Nouveau message client',
            'message' => $senderName.' a répondu au ticket '.$ref.'.',
            'url' => route('agent.tickets.show', $this->message->ticket_id),
        ];
    }
}
