<?php

use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;

test('tickets index shows tickets for current team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->for($user, 'owner')->create();
    $team->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_team_id' => $team->id]);

    $ticket = Ticket::factory()->for($team)->for($user)->create([
        'subject' => 'Team Ticket',
    ]);

    $otherTeam = Team::factory()->create();
    $otherUser = User::factory()->create();
    $otherTeam->users()->attach($otherUser, ['role' => 'owner']);

    $otherTicket = Ticket::factory()->for($otherTeam)->for($otherUser)->create([
        'subject' => 'Other Ticket',
    ]);

    $this->actingAs($user)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Team Ticket')
        ->assertDontSee('Other Ticket');
});

test('tickets index is empty when current team is stale', function () {
    $user = User::factory()->create();
    $team = Team::factory()->for($user, 'owner')->create();
    $team->users()->attach($user, ['role' => 'owner']);

    Ticket::factory()->for($team)->for($user)->create([
        'subject' => 'Valid Ticket',
    ]);

    $staleTeam = Team::factory()->create();
    $user->update(['current_team_id' => $staleTeam->id]);

    $this->actingAs($user)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertDontSee('Valid Ticket');
});
