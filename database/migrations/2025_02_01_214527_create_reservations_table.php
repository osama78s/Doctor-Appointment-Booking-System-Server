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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->enum('status',['pendding','complete','cancel','finished'])->default('pendding');
            $table->enum('review',['0','1'])->default(0)->nullable();

            $table->foreignId('appointment_id')->constrained('appointments','id')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users','id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users','id')->cascadeOnDelete();
            $table->foreignId('feese_id')->constrained('feeses','id')->cascadeOnDelete();
            $table->foreignId('day_id')->constrained('days','id')->cascadeOnDelete();

            $table->enum('payment_method', ['paypal', 'stripe', 'cache'])->nullable();
            $table->enum('is_paid', ['paid', 'not_paid'])->default('not_paid');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
