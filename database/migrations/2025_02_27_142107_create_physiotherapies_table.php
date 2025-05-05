<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physiotherapy', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('scheme_id');
            $table->date('visit_date');
            $table->integer('pain_level')->nullable()->checkBetween(0, 10);
            $table->integer('mobility_score')->nullable()->checkBetween(1, 5);
            $table->integer('range_of_motion')->nullable()->checkBetween(1, 5);
            $table->integer('strength')->nullable()->checkBetween(0, 5);
            $table->integer('balance')->nullable()->checkBetween(1, 5);
            $table->integer('walking_ability')->nullable()->checkBetween(0, 60);
            $table->string('posture_assessment', 50)->nullable();
            $table->string('exercise_type', 50)->nullable();
            $table->integer('frequency_per_week')->nullable()->checkBetween(1, 7);
            $table->integer('duration_per_session')->nullable()->checkBetween(1, 60);
            $table->integer('intensity')->nullable()->checkBetween(1, 10);
            $table->integer('pain_level_before_exercise')->nullable()->checkBetween(0, 10);
            $table->integer('pain_level_after_exercise')->nullable()->checkBetween(0, 10);
            $table->integer('fatigue_level_before_exercise')->nullable()->checkBetween(0, 10);
            $table->integer('fatigue_level_after_exercise')->nullable()->checkBetween(0, 10);
            $table->integer('post_exercise_recovery_time')->nullable()->checkBetween(1, 60);
            $table->integer('functional_independence')->nullable()->checkBetween(1, 5);
            $table->boolean('joint_swelling')->nullable();
            $table->boolean('muscle_spasms')->nullable();
            $table->integer('progress')->nullable()->checkBetween(0, 5);
            $table->text('treatment')->nullable();
            $table->text('challenges')->nullable();
            $table->text('adjustments_made')->nullable();
            $table->string('calcium_levels', 10)->nullable()->checkIn(['Normal', 'High', 'Low']);
            $table->string('phosphorous_levels', 10)->nullable()->checkIn(['Normal', 'High', 'Low']);
            $table->string('vit_d_levels', 10)->nullable()->checkIn(['Normal', 'High', 'Low']);
            $table->string('cholesterol_levels', 10)->nullable()->checkIn(['Normal', 'High', 'Low']);
            $table->string('iron_levels', 10)->nullable()->checkIn(['Normal', 'High', 'Low']);
            $table->integer('heart_rate')->nullable()->checkBetween(40, 200);
            $table->integer('blood_pressure_systolic')->nullable()->checkBetween(80, 180);
            $table->integer('blood_pressure_diastolic')->nullable()->checkBetween(50, 120);
            $table->integer('oxygen_saturation')->nullable()->checkBetween(70, 100);
            $table->string('hydration_level', 10)->nullable()->checkIn(['Normal', 'Low', 'High']);
            $table->integer('sleep_quality')->nullable()->checkBetween(1, 10);
            $table->integer('stress_level')->nullable()->checkBetween(0, 10);
            $table->text('medication_usage')->nullable();
            $table->text('therapist_notes')->nullable();
            $table->decimal('revenue', 10, 2);
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physiotherapy');
    }
};
