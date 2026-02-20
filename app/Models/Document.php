<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'body',
        'embedding',
        'source_url',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
