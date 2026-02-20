<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Document;
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
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $team = Team::factory()
            ->personal()
            ->for($owner, 'owner')
            ->create([
                'name' => "{$owner->name}'s Team",
            ]);

        $member = User::factory()->create([
            'name' => 'Second User',
            'email' => 'second@example.com',
        ]);

        $team->users()->attach($owner, ['role' => 'owner']);
        $team->users()->attach($member, ['role' => 'member']);

        $owner->forceFill(['current_team_id' => $team->id])->save();
        $member->forceFill(['current_team_id' => $team->id])->save();

        $tags = TicketTag::factory()->count(6)->create();

        Document::factory()
            ->count(random_int(3, 5))
            ->for($team)
            ->create();

        $tickets = Ticket::factory()
            ->count(10)
            ->for($team)
            ->create();

        $ticketOwners = collect([$owner, $member]);

        foreach ($tickets as $index => $ticket) {
            $ticketOwner = $ticketOwners[$index % $ticketOwners->count()];

            $ticket->update([
                'user_id' => $ticketOwner->id,
            ]);

            TicketMessage::factory()
                ->count(random_int(1, 3))
                ->for($ticket)
                ->for($ticketOwner)
                ->create();

            $ticket->tags()->attach(
                $tags->random(random_int(1, 3))->pluck('id')->all()
            );
        }
    }
}
