<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingReviewProcess extends Model
{
    protected $table = 'odms_accounting_review_processes';

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'accounting_id',
        'date_returned',
        'date_received',
        'remarks',
    ];
}