<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketTag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@acme.test',
        ]);

        $member = User::factory()->create([
            'name' => 'Mark Reyes',
            'email' => 'mark@acme.test',
        ]);

        $team = Team::factory()
            ->personal()
            ->for($owner, 'owner')
            ->create([
                'name' => 'Acme Support Ops',
            ]);

        $team->users()->attach($owner, ['role' => 'owner']);
        $team->users()->attach($member, ['role' => 'member']);

        $owner->forceFill(['current_team_id' => $team->id])->save();
        $member->forceFill(['current_team_id' => $team->id])->save();

        $tags = TicketTag::insert([
            ['name' => 'billing'],
            ['name' => 'refund'],
            ['name' => 'outage'],
            ['name' => 'api'],
            ['name' => 'security'],
            ['name' => 'login'],
            ['name' => 'feature-request'],
            ['name' => 'performance'],
            ['name' => 'invoice'],
            ['name' => 'cancellation'],
        ]);

        $tagIds = TicketTag::query()->pluck('id', 'name');

        Document::query()->insert([
            [
                'team_id' => $team->id,
                'title' => 'Billing and Refund Policy',
                'body' => 'Refunds are issued for duplicate charges and billing errors. Refund requests must be submitted within 30 days of the charge date. Enterprise plans include SLA guarantees but are non-refundable after activation.',
                'embedding' => null,
                'source_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'title' => 'Login & Authentication Issues',
                'body' => 'If users cannot log in, verify that their email is confirmed and that the password reset process has completed successfully. Repeated 403 responses may indicate expired or revoked API tokens.',
                'embedding' => null,
                'source_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'title' => 'API Error Reference',
                'body' => '403 indicates unauthorized access. 401 indicates missing credentials. 500 indicates internal server errors. Contact support if errors persist for more than 15 minutes.',
                'embedding' => null,
                'source_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'title' => 'Data Isolation & Security Practices',
                'body' => 'Each team’s data is logically isolated. If data appears from another team, contact security immediately as this may indicate a caching issue.',
                'embedding' => null,
                'source_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'title' => 'Feature Requests Process',
                'body' => 'Feature requests are reviewed quarterly. Dark mode and bulk export are currently under consideration.',
                'embedding' => null,
                'source_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $tickets = [
            [
                'subject' => 'Charged twice this month',
                'user_id' => $owner->id,
                'message' => 'I was charged twice for my Pro subscription this month. I checked my bank statement and there are two identical charges from SupportDesk. Please fix this and refund the extra payment immediately.',
                'tags' => ['billing', 'refund'],
            ],
            [
                'subject' => 'Cannot log into my account',
                'user_id' => $member->id,
                'message' => 'I reset my password but I still can\'t log in. It keeps saying invalid credentials even though I just changed it. Is something wrong with your authentication system?',
                'tags' => ['login'],
            ],
            [
                'subject' => 'Can you add dark mode?',
                'user_id' => $owner->id,
                'message' => 'It would be great if SupportDesk had a dark mode option. Our team works late and the bright interface is hard on the eyes.',
                'tags' => ['feature-request'],
            ],
            [
                'subject' => 'Entire dashboard is down',
                'user_id' => $member->id,
                'message' => 'None of our team members can access the dashboard. We are getting a 500 error across the entire app. This is blocking our support operations.',
                'tags' => ['outage', 'performance'],
            ],
            [
                'subject' => 'Pricing for enterprise plan',
                'user_id' => $owner->id,
                'message' => 'We’re evaluating support platforms and would like more information about your enterprise plan pricing and SLA guarantees.',
                'tags' => [],
            ],
            [
                'subject' => 'Requesting refund after cancellation',
                'user_id' => $member->id,
                'message' => 'I canceled my subscription yesterday but was still charged today. I would like a refund for this billing cycle.',
                'tags' => ['billing', 'refund'],
            ],
            [
                'subject' => 'API returning 403 on valid token',
                'user_id' => $owner->id,
                'message' => 'Our integration started failing this morning. The API is returning 403 responses even though the token is valid and hasn\'t expired.',
                'tags' => ['api'],
            ],
            [
                'subject' => 'Possible data exposure',
                'user_id' => $member->id,
                'message' => 'I noticed another company\'s tickets briefly appearing in our dashboard before refreshing the page. This is extremely concerning from a security standpoint.',
                'tags' => ['security'],
            ],
            [
                'subject' => 'How do I delete our account permanently?',
                'user_id' => $owner->id,
                'message' => 'We\'re considering switching providers. What is the process for permanently deleting our account and all associated data?',
                'tags' => ['cancellation'],
            ],
            [
                'subject' => 'Dashboard loads very slowly',
                'user_id' => $member->id,
                'message' => 'The dashboard has been taking 15–20 seconds to load today. It normally loads instantly. Is there a performance issue?',
                'tags' => ['performance'],
            ],
            [
                'subject' => 'Cannot download last month’s invoice',
                'user_id' => $owner->id,
                'message' => 'I need a copy of our invoice for last month, but the download link is missing from the billing page.',
                'tags' => ['invoice', 'billing'],
            ],
            [
                'subject' => 'Do you support bulk ticket export?',
                'user_id' => $member->id,
                'message' => 'We need to export all tickets from the last 12 months for compliance purposes. Is there a bulk export feature?',
                'tags' => ['feature-request'],
            ],
        ];

        foreach ($tickets as $ticketData) {
            $ticket = Ticket::create([
                'team_id' => $team->id,
                'user_id' => $ticketData['user_id'],
                'subject' => $ticketData['subject'],
                'status' => 'open',
                'priority' => 3,
                'department' => null,
                'sentiment' => null,
                'ai_tags' => [],
            ]);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticketData['user_id'],
                'role' => 'user',
                'body' => $ticketData['message'],
            ]);

            if (! empty($ticketData['tags'])) {
                $ticket->tags()->attach(
                    $tagIds->only($ticketData['tags'])->values()->all()
                );
            }
        }
    }
}
