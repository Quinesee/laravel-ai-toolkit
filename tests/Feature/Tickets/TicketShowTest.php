<?php

use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Livewire\Livewire;

test('ticket show is displayed for team member', function () {
    $user = User::factory()->create();
    $team = Team::factory()->for($user, 'owner')->create();
    $team->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_team_id' => $team->id]);

    $ticket = Ticket::factory()->for($team)->for($user)->create([
        'subject' => 'Visible Ticket',
    ]);

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertSee('Visible Ticket');
});

test('ticket show returns 404 for other team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->for($user, 'owner')->create();
    $team->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_team_id' => $team->id]);

    $otherTeam = Team::factory()->create();
    $otherUser = User::factory()->create();
    $otherTeam->users()->attach($otherUser, ['role' => 'owner']);

    $ticket = Ticket::factory()->for($otherTeam)->for($otherUser)->create();

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertNotFound();
});

test('user can post a message on a ticket', function () {
    $user = User::factory()->create();
    $team = Team::factory()->for($user, 'owner')->create();
    $team->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_team_id' => $team->id]);

    $ticket = Ticket::factory()->for($team)->for($user)->create();

    $this->actingAs($user);

    Livewire::test('pages::tickets.show', ['ticket' => $ticket])
        ->set('messageBody', 'Hello from the customer')
        ->call('addMessage')
        ->assertHasNoErrors();

    expect(TicketMessage::where('ticket_id', $ticket->id)->count())->toBe(1);
    $message = TicketMessage::where('ticket_id', $ticket->id)->first();

    expect($message->body)->toBe('Hello from the customer');
    expect($message->role)->toBe('user');
    expect($message->user_id)->toBe($user->id);
});
