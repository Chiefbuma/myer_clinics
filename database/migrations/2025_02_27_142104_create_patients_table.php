<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient', function (Blueprint $table) {
            $table->id('patient_id');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->date('dob');
            $table->string('gender', 10);
            $table->integer('age');
            $table->string('location', 100);
            $table->string('phone_no', 20);
            $table->string('email', 255);
            $table->string('patient_no', 20)->unique();
            $table->string('diagnosis', 255)->nullable();
            $table->string('patient_status', 20);
            $table->unsignedBigInteger('cohort_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('diagnosis_id')->nullable();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient');
    }
};
