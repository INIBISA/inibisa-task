<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['product_id', 'milestone_id', 'created_by', 'title', 'description', 'status', 'priority', 'start_date', 'due_date', 'estimated_minutes', 'position', 'is_blocked', 'blocked_reason', 'week_number'];

    protected $casts = ['start_date' => 'date', 'due_date' => 'date', 'is_blocked' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
