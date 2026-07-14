<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Ticket $ticket;
    public string $messageBody = '';

    public function mount(Ticket $ticket): void
    {
        if (Gate::denies('view', $ticket)) {
            abort(404);
        }

        $this->ticket = $ticket;
    }

    #[Computed]
    public function ticketMessages()
    {
        return $this->ticket->messages()->with('user')->oldest()->get();
    }

    #[Computed]
    public function tags()
    {
        return $this->ticket->tags()->orderBy('name')->get();
    }

    public function addMessage(): void
    {
        Gate::authorize('view', $this->ticket);

        $validated = $this->validate([
            'messageBody' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'role' => 'user',
            'body' => $validated['messageBody'],
        ]);

        $this->reset('messageBody');
        $this->dispatch('message-posted');
    }
}; ?>

<section class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="xl">{{ $ticket->subject }}</flux:heading>
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Status: :status', ['status' => ucfirst($ticket->status)]) }}
            · {{ __('Priority: :priority', ['priority' => $ticket->priority]) }}
            @if ($ticket->department)
                · {{ __('Dept: :department', ['department' => ucfirst($ticket->department)]) }}
            @endif
            @if ($ticket->sentiment)
                · {{ __('Sentiment: :sentiment', ['sentiment' => ucfirst($ticket->sentiment)]) }}
            @endif
        </flux:text>
    </div>

    @if ($this->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            @foreach ($this->tags as $tag)
                <span
                    class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="space-y-5">
        <form
            action="{{ route('tickets.ai.triage', ['ticket' => $this->ticket->id]) }}"
            method="POST"
        >
            @csrf
            <flux:button
                type="submit"
                variant="primary"
            >
                {{ __('Triage') }}
            </flux:button>
        </form>
    </div>

    <div class="space-y-4">
        <flux:heading size="sm">{{ __('Conversation') }}</flux:heading>

        <div class="space-y-3">
            @forelse ($this->ticketMessages as $message)
                <div
                    class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ ucfirst($message->role) }}
                            @if ($message->user)
                                · {{ $message->user->name }}
                            @endif
                        </flux:text>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $message->created_at->diffForHumans() }}
                        </flux:text>
                    </div>
                    <div class="mt-2 whitespace-pre-line text-zinc-800 dark:text-zinc-200">
                        {{ $message->body }}
                    </div>
                </div>
            @empty
                <div
                    class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ __('No messages yet. Start the conversation below.') }}
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading
            class="mb-3"
            size="sm"
        >{{ __('Add a message') }}</flux:heading>
        <form
            class="space-y-3"
            wire:submit="addMessage"
        >
            <div>
                <textarea
                    class="w-full rounded-lg border border-zinc-200 bg-white p-3 text-sm text-zinc-900 shadow-sm focus:border-zinc-400 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    placeholder="{{ __('Write a reply...') }}"
                    rows="4"
                    wire:model="messageBody"
                ></textarea>
                @error('messageBody')
                    <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                >
                    {{ __('Post Message') }}
                </flux:button>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Visible to teammates on this ticket.') }}
                </flux:text>
            </div>
        </form>
    </div>
</section>
