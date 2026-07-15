<?php

namespace App\Ai\Agents;

use App\Models\Ticket;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[UseCheapestModel]
#[MaxTokens(1500)]
class TicketAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function __construct(
        public readonly int $ticketId,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $context = $this->getTicketContext();

        return <<<PROMPT
        You are a support ticket assistant. You will be given a support ticket and you will respond to the user in a helpful and professional manner. You will also be given the ticket's subject and body. You will not make up any information that is not provided in the ticket. You will not provide any information that is not relevant to the ticket. You will not provide any information that is not helpful to the user. You will not provide any information that is not professional. You will not provide any information that is not accurate. You will not provide any information that is not complete. You will not provide any information that is not clear. You will not provide any information that is not concise. You will not provide any information that is not relevant to the user's question or concern.

        $context
        PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    public function getTicketContext(): string
    {
        $ticket = Ticket::with([
            'messages' => fn($query) => $query->latest()->limit(5),
            'tags',
        ])->find($this->ticketId);

        if (!$ticket) {
            return "Ticket context unavailable: Ticket not found.";
        }

        $tags = $ticket->tags->pluck('name')->join(', ');
        $department = $ticket->department?->name ?? 'N/A';
        $sentiment = $ticket->sentiment ?? 'N/A';
        $tagsText = $tags ?: 'none';
        $messages = $ticket->messages->reverse()
            ->map(fn($message) => sprintf('%s: %s', $message->role, $message->body))
            ->implode("\n");

        return <<<CONTEXT
        Ticket Context:
            - Subject: {$ticket->subject}
            - Status: {$ticket->status}
            - Priority: {$ticket->priority}
            - Department: {$department}
            - Sentiment: {$sentiment}
            - Tags: {$tagsText}
            
        Recent Messages:
            {$messages}
        CONTEXT;
    }
}
