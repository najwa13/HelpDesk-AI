<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Ticket $ticket,
        private ?User $assignedBy = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $ref = '#TK-'.str_pad((string) $this->ticket->id, 4, '0', STR_PAD_LEFT);

        return [
            'type' => 'ticket_assigned',
            'ticket_id' => $this->ticket->id,
            'ticket_ref' => $ref,
            'ticket_title' => $this->ticket->titre,
            'title' => 'Nouveau ticket assigné',
            'message' => "Le ticket {$ref} vous a été assigné.",
            'url' => route('agent.tickets.show', $this->ticket),
            'assigned_by' => $this->assignedBy?->id,
        ];
    }
}
