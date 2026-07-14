<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiRun extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'ticket_id',
        'feature_key',
        'status',
        'provider',
        'model',
        'input_hash',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function usages(): HasOne
    {
        return $this->hasOne(AiUsages::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
