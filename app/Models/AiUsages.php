<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsages extends Model
{
    protected $fillable = [
        'ai_run_id',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AiRun::class, 'ai_run_id');
    }
}
