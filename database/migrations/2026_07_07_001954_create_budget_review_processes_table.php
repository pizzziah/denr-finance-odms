<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('budget_review_processes', function (Blueprint $table) {

        $table->id();

        $table->unsignedBigInteger('budget_id');

        $table->dateTime('date_returned')->nullable();

        $table->dateTime('date_received')->nullable();

        $table->text('remarks')->nullable();

        $table->timestamps();

        $table->foreign('budget_id')
              ->references('budget_id')
              ->on('odms_budget')
              ->onDelete('cascade');
    });
}
public function down(): void
{
    Schema::dropIfExists('budget_review_processes');
}
};
