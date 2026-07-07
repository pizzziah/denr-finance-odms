<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetReviewProcess extends Model
{
    protected $fillable = [
        'budget_id',
        'date_returned',
        'date_received',
        'remarks',
    ];
}
