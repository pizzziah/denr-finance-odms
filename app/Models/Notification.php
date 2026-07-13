<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'odms_budget_notifications';

    protected $fillable = [
        'title',
        'message',
        'type',
        'related_id',
        'reference_id',
        'target_role',
        'user_id',
        'due_date',
        'priority',
        'is_read',
    ];
}
