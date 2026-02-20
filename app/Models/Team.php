<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'personal_team',
        'owner_user_id',
    ];

    protected $casts = [
        'personal_team' => 'bool',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('role');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
