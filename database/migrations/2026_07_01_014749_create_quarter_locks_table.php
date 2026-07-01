<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quarter_locks', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('quarter');
            $table->enum('status', ['open', 'locked'])->default('open');
            $table->boolean('requires_admin_unlock')->default(false);
            $table->timestamps();
            
            // Rejection checkpoint protecting against duplicates
            $table->unique(['year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarter_locks');
    }
};