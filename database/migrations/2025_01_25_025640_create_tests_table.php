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
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            // $table->string('name_ar');
            // $table->text('descreption_ar');
            // $table->string('name_en');
            // $table->text('descreption_en');

            $table->string('name');
            $table->text('descreption');
            $table->text('lang');
            $table->text('rand_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
