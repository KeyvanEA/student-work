<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete()->unique();
            $table->enum('status', ['in_progress', 'submitted', 'revision_requested','completed', 'cancelled','disputed'
            ])->default('in_progress');
            $table->integer('amount');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->dateTime('deadline');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
