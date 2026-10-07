<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['audience_id', 'owner_id', 'name', 'slug', 'description', 'problem', 'solution', 'target_user', 'business_model', 'price_idea', 'stage', 'priority', 'status', 'target_launch'];

    protected $casts = ['target_launch' => 'date'];

    public function audience()
    {
        return $this->belongsTo(Audience::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
}
