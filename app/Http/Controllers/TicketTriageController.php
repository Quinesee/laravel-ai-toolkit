<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketTriager;
use App\Models\AiRun;
use App\Models\AiUsages;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketTriageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket)
    {

        $run = AiRun::create([
            'team_id' => $ticket->team_id,
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'feature_key' => 'ticket-triage',
            'status' => 'running',
            'provider' => 'openai',
            'model' => null,
            'input_hash' => md5($ticket->subject . $ticket->messages()->latest()->first()?->body),
            'started_at' => now(),
        ]);

        try {
            $response = (new TicketTriager)->prompt(
                "Subject: {$ticket->subject}\n\n{$ticket->messages()->latest()->first()?->body}"
            );

            $ticket->update([
                'priority' => $response['priority'],
                'department' => $response['department'],
                'sentiment' => $response['sentiment'],
                'ai_tags' => $response['tags'],
            ]);

            if (!empty($response['summary'])) {
                $ticket->messages()->create([
                    'user_id' => null,
                    'role' => 'system',
                    'body' => 'AI Summary: ' . $response['summary'],
                ]);
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

            return response()->json([
                'status' => 'ok',
                'data' => $response,
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }
}
