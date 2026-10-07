<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_archived', 'position'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
