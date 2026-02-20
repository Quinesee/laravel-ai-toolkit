<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'subject',
        'status',
        'priority',
        'department',
        'sentiment',
        'ai_tags',
    ];

    protected $casts = [
        'ai_tags' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function tags()
    {
        return $this->belongsToMany(TicketTag::class)
            ->withTimestamps();
    }
}
