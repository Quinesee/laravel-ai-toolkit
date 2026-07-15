<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketAssistant;
use App\Models\AiRun;
use App\Models\AiUsages;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketChatController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $agent = new TicketAssistant(ticketId: $ticket->id);
        $prompt = "\n\nUser message:\n" . $request->string('message');

        $run = AiRun::create([
            'team_id' => $ticket->team_id,
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'feature_key' => 'ticket_chat',
            'status' => 'running',
            'provider' => 'openai',
            'model' => null,
            'input_hash' => sha1($ticket->id . '|' . $request->string('message')),
            'started_at' => now(),
        ]);

        try {
            if ($ticket->ai_conversation_id) {
                $response = $agent->continue($ticket->ai_conversation_id, as: $request->user())
                    ->prompt($prompt);
            } else {
                $response = $agent->forUser($request->user())
                    ->prompt($prompt);

                $ticket->update([
                    'ai_conversation_id' => $response->conversationId,
                ]);
            }
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }

        $run->update([
            'status' => 'succeeded',
            'finished_at' => now(),
        ]);

        if (isset($response->usage)) {
            $usage = $response->usage;
            AiUsages::create([
                'ai_run_id' => $run->id,
                'prompt_tokens' => $usage->prompt_tokens ?? 0,
                'completion_tokens' => $usage->completion_tokens ?? 0,
                'total_tokens' => $usage->total_tokens ?? 0,
                'cost_usd' => $usage->cost_usd ?? 0,
            ]);
        }

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'role' => 'user',
            'body' => $request->string('message'),
        ]);

        $ticket->messages()->create([
            'user_id' => null,
            'role' => 'agent',
            'body' => (string) $response,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => (string) $response,
        ]);
    }
}
