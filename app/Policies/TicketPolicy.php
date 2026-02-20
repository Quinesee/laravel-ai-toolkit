<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->teams()->whereKey($ticket->team_id)->exists();
    }

    public function useAi(User $user, Ticket $ticket): bool
    {
        return $user->teams()->whereKey($ticket->team_id)->exists();
    }
}
