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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique();
            $table->enum('gender',['M','F'])->default('M')->nullable();
            $table->string('password');
            
            
            $table->integer('code')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('code_expired_at')->nullable();
            $table->string('image')->default('default.jpg')->nullable();
            $table->enum('status',['active','un_active'])->default('un_active')->nullable();
            $table->enum('role',['user','admin','doctor'])->default('user')->nullable();
            $table->integer('age')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // DOCTOR COLUMNS 
            $table->text('desc')->nullable();
            $table->string('clinic_address')->nullable();
            $table->integer('ex_years')->nullable();
            $table->foreignId('specialization_id')->nullable()->constrained()->cascadeOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
