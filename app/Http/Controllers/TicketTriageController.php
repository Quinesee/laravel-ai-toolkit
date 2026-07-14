<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketTriager;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketTriageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket)
    {
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

            return response()->json([
                'status' => 'ok',
                'data' => $response,
            ]);
        } catch (\Throwable $e) {
            // TODO: something here
        }
    }
}
