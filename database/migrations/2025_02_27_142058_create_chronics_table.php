<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chronic', function (Blueprint $table) {
            $table->id('chronic_id');
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->unsignedBigInteger('speciality_id')->nullable();
            $table->date('refill_date')->nullable();
            $table->text('compliance')->nullable();
            $table->text('exercise')->nullable();

            $table->text('clinical_goals')->nullable();
            table->text('psychosocial')->nullable();
            table->text('nutrition_follow_up')->nullable();
            $table->date('annual_check_up')->nullable();
            $table->date('specialist_review')->nullable();
            $table->text('vitals_monitoring')->nullable();
            $table->decimal('revenue', 10, 2)->nullable();
            $table->text('remote_vital_monitor')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->date('last_visit')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronic');
    }
};
