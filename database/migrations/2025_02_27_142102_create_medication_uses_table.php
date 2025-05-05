<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_use', function (Blueprint $table) {
            $table->id('medication_use_id');
            $table->integer('days_supplied')->nullable();
            $table->integer('no_pills_dispensed')->nullable();
            $table->string('frequency', 255)->nullable();
            $table->unsignedBigInteger('medication_id');
            $table->unsignedBigInteger('patient_id');
            $table->date('visit_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_use');
    }
};
