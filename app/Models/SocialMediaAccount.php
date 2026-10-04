<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMediaAccount extends Model
{
    protected $fillable = ['platform', 'name', 'url', 'login_email', 'password', 'is_active'];

    protected function casts(): array
    {
        return ['password' => 'encrypted', 'is_active' => 'boolean'];
    }
}
