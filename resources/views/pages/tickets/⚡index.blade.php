<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $perPage = 15;

    #[Computed]
    public function hasValidTeam(): bool
    {
        $user = Auth::user();

        if (! $user?->current_team_id) {
            return false;
        }

        return $user->teams()->whereKey($user->current_team_id)->exists();
    }

    #[Computed]
    public function tickets()
    {
        $user = Auth::user();
        $currentTeamId = $user?->current_team_id;

        if (! $currentTeamId || ! $this->hasValidTeam) {
            return Ticket::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return Ticket::query()
            ->where('team_id', $currentTeamId)
            ->whereHas('team.users', fn ($query) => $query->whereKey($user->id))
            ->latest()
            ->paginate($this->perPage);
    }
}; ?>

<section class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Tickets') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('All tickets for your current team.') }}
            </flux:text>
        </div>
    </div>

    @if (! $this->hasValidTeam)
        <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('No valid current team is set for this user.') }}
        </div>
    @else
        <div class="space-y-3">
            @forelse ($this->tickets as $ticket)
                <a
                    href="{{ route('tickets.show', $ticket) }}"
                    class="block rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
                    wire:navigate
                >
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <flux:heading size="sm">{{ $ticket->subject }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Status: :status', ['status' => ucfirst($ticket->status)]) }}
                                · {{ __('Priority: :priority', ['priority' => $ticket->priority]) }}
                                @if ($ticket->department)
                                    · {{ __('Dept: :department', ['department' => ucfirst($ticket->department)]) }}
                                @endif
                            </flux:text>
                        </div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $ticket->created_at->diffForHumans() }}
                        </flux:text>
                    </div>
                </a>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ __('No tickets found for this team yet.') }}
                </div>
            @endforelse
        </div>

        <div>
            {{ $this->tickets->links() }}
        </div>
    @endif
</section>
