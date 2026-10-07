<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = ['product_id', 'name', 'description', 'start_date', 'due_date', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
