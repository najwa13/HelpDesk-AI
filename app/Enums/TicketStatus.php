<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Ouvert = 'ouvert';
    case EnCours = 'en_cours';
    case Resolu = 'resolu';
    case Ferme = 'ferme';

    public function transitionsValides(): array
    {
        return match ($this) {
            self::Ouvert => [self::EnCours],
            self::EnCours => [self::Resolu],
            self::Resolu => [self::Ferme, self::EnCours],
            self::Ferme => [],
        };
    }
}
