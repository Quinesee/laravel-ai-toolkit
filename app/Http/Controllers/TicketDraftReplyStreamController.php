<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketAssistant;
use App\Models\AiRun;
use App\Models\AiUsages;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Laravel\Ai\Responses\StreamedAgentResponse;

class TicketDraftReplyStreamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket)
    {
        $agent = new TicketAssistant($ticket->id);
        $prompt = 'Draft a concise, friendly reply to the most recent user message:';

        $run = AiRun::create([
            'team_id' => $ticket->team_id,
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'feature_key' => 'draft_reply',
            'status' => 'running',
            'provider' => 'openai',
            'model' => null,
            'input_hash' => sha1($ticket->id . '|' . $request->string('message')),
            'started_at' => now(),
        ]);

        $stream = $agent->stream($prompt);

        $stream->then(function (StreamedAgentResponse $response) use ($run) {
            $run->update([
                'status' => 'succeeded',
                'finished_at' => now(),
                'output_text' => $response->text,
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
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
        });

        return $stream;
    }
}
