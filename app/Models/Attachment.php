<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['user_id', 'path', 'name', 'mime_type', 'size'];

    public function attachable()
    {
        return $this->morphTo();
    }
}
