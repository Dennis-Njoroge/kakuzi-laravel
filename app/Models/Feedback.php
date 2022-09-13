<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'from', 'subject', 'message', 'reply', 'date_reply'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
