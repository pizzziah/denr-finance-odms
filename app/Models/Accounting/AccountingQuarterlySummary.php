<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccountingQuarterlySummary extends Model
{
    protected $table = 'odms_accounting_2026_q1'; 
    protected $primaryKey = 'q1_id';
    public $timestamps = false;

    protected $fillable = [
        'emds_date',
        'date_processed',
        'particulars',
        'amount',
        'nca_nta_received',
        'nca_nta_downloaded',
        'balance',
        'ada_no',
        'remarks'
    ];

    /**
     * Set the database table dynamically at runtime and synchronize primary keys.
     */
    public function setQuarterTable($quarter)
    {
        $mapping = [
            1 => ['table' => 'odms_accounting_2026_q1', 'pk' => 'q1_id'],
            2 => ['table' => 'odms_accounting_2026_q2', 'pk' => 'q2_id'],
            3 => ['table' => 'odms_accounting_2026_q3', 'pk' => 'q3_id'],
            4 => ['table' => 'odms_accounting_2026_q4', 'pk' => 'q4_id'],
        ];

        $target = $mapping[$quarter] ?? $mapping[1];
        
        // Ensure table exists dynamically in the database
        $this->ensureTableExists($target['table'], $target['pk']);

        $this->setTable($target['table']);
        $this->primaryKey = $target['pk'];
        
        return $this;
    }

    /**
     * Programmatically checks and auto-creates quarter tables on demand using schema configurations.
     */
    protected function ensureTableExists($tableName, $primaryKeyName)
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($primaryKeyName) {
                $table->bigIncrements($primaryKeyName);
                $table->string('emds_date', 100)->nullable();
                $table->string('date_processed', 100)->nullable();
                $table->string('particulars', 255)->nullable();
                $table->string('amount', 50)->nullable();
                $table->string('nca_nta_received', 100)->nullable();
                $table->string('nca_nta_downloaded', 100)->nullable();
                $table->string('balance', 50)->nullable();
                $table->string('ada_no', 50)->nullable();
                $table->string('remarks', 255)->nullable();
            });
        }
    }

    /**
     * Helper to clean numeric string formatting out into standard floating points.
     */
    public static function parseMoney($value)
    {
        if (empty($value)) return 0.00;
        $cleaned = str_replace(['₱', ',', ' ', '(', ')'], '', $value);
        $floatVal = is_numeric($cleaned) ? (float) $cleaned : 0.00;
        return str_contains($value, '(') ? -$floatVal : $floatVal;
    }
}