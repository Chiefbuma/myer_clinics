<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition', function (Blueprint $table) {
            $table->id('nutrition_id');
            $table->text('scheme_id')->nullable();
            $table->text('patient_id')->nullable();
            $table->date('last_visit')->nullable();
            $table->date('next_review')->nullable();
            $table->double('muscle_mass')->nullable();
            $table->double('bone_mass')->nullable();
            $table->double('weight')->nullable();
            $table->double('BMI')->nullable();
            $table->double('total_body_fat')->nullable();
            $table->double('visceral_fat')->nullable();
            $table->text('weight_remarks')->nullable();
            $table->text('physical_activity')->nullable();
            $table->text('meal_plan_set_up')->nullable();
            $table->text('nutrition_adherence')->nullable();
            $table->text('nutrition_assessment_remarks')->nullable();
            $table->double('revenue')->nullable();
            $table->date('visit_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition');
    }
};
