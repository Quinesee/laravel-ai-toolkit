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
            'name' => 'Jeremy',
            'email' => 'jeremy@example.com',
        ]);

        $team = Team::factory()
            ->personal()
            ->for($owner, 'owner')
            ->create([
                'name' => "{$owner->name}'s Team",
            ]);

        $members = collect([
            User::factory()->create([
                'name' => 'Jim',
                'email' => 'jim@example.com',
            ]),
            User::factory()->create([
                'name' => 'Ava',
                'email' => 'ava@example.com',
            ]),
            User::factory()->create([
                'name' => 'Miles',
                'email' => 'miles@example.com',
            ]),
            User::factory()->create([
                'name' => 'Priya',
                'email' => 'priya@example.com',
            ]),
        ]);

        $team->users()->attach($owner, ['role' => 'owner']);

        foreach ($members as $member) {
            $team->users()->attach($member, ['role' => 'member']);
        }

        $owner->forceFill(['current_team_id' => $team->id])->save();

        foreach ($members as $member) {
            $member->forceFill(['current_team_id' => $team->id])->save();
        }

        $tags = TicketTag::factory()->count(6)->create();

        Document::factory()
            ->count(random_int(3, 5))
            ->for($team)
            ->create();

        $tickets = Ticket::factory()
            ->count(10)
            ->for($team)
            ->create();

        $ticketOwners = collect([$owner])->merge($members);

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
