<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccountingQuarterlySummary extends Model {
    protected $table = 'odms_accounting_2026_q1';
    protected $primaryKey = 'q1_id';
    public $timestamps = false;

  protected $fillable = [
    'emds_date',
    'date_processed',
    'dv_no',
    'particulars',
    'transaction_type',
    'amount',
    'balance',
    'ada_no',
    'remarks',
  ];

  /**
   * Set the database table dynamically at runtime and synchronize primary keys across years.
   */
  public function setQuarterTable($quarter, $year = null) {
    // Fallback to the current year if none is supplied
    $year = $year ?? date('Y');

    // Dynamically compute names based on the selected year and quarter context
    $tableName = "odms_accounting_{$year}_q{$quarter}";
    $pkName = "q{$quarter}_id";

    // Ensure table exists dynamically in the database
    $this->ensureTableExists($tableName, $pkName);
    $this->setTable($tableName);
    $this->primaryKey = $pkName;
    
    return $this;
  }

  /**
   * Programmatically checks and auto-creates quarter tables on demand using schema configurations.
   */
  protected function ensureTableExists($tableName, $primaryKeyName) {
    if (! Schema::hasTable($tableName)) {
      Schema::create($tableName, function (Blueprint $table) use ($primaryKeyName) {
        $table->bigIncrements($primaryKeyName);
        $table->string('emds_date', 100)->nullable();
        $table->string('date_processed', 100)->nullable();
        $table->string('dv_no', 100)->nullable();
        $table->string('particulars', 255)->nullable();
        $table->string('transaction_type', 100)->nullable();
        $table->float('amount')->nullable();
        $table->float('balance')->nullable();
        $table->string('ada_no', 100)->nullable();
        $table->string('remarks', 255)->nullable();
      });
    }
  }

  /**
   * Helper to clean numeric string formatting out into standard floating points.
   */
  public static function parseMoney($value) {
    if (empty($value)) {
      return 0.00;
    }
    
    $cleaned = str_replace(['₱', ',', ' ', '(', ')'], '', $value);
    $floatVal = is_numeric($cleaned) ? (float) $cleaned : 0.00;

    return str_contains($value, '(') ? -$floatVal : $floatVal;
  }
}
