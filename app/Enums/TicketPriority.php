<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Basse = 'basse';
    case Moyenne = 'moyenne';
    case Haute = 'haute';
}
