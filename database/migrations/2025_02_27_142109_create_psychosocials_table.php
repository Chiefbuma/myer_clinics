<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychosocial', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('patient_id');
            $table->date('last_visit')->nullable();
            $table->date('next_review');
            $table->string('educational_level', 255)->nullable();
            $table->string('career_business', 255)->nullable();
            $table->string('marital_status', 255)->nullable();
            $table->string('relationship_status', 255)->nullable();
            $table->string('primary_relationship_status', 255)->nullable();
            $table->string('ability_to_enjoy_leisure_activities', 255)->nullable();
            $table->string('spirituality', 255)->nullable();
            $table->string('level_of_self_esteem', 255)->nullable();
            $table->string('sex_life', 255)->nullable();
            $table->string('ability_to_cope_recover_disappointments', 255)->nullable();
            $table->string('rate_of_personal_development_growth', 255)->nullable();
            $table->string('achievement_of_balance_in_life', 255)->nullable();
            $table->string('social_support_system', 255)->nullable();
            $table->string('substance_use', 255)->nullable();
            $table->string('substance_used', 255)->nullable();
            $table->text('assessment_remarks')->nullable();
            $table->integer('revenue')->nullable();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->date('visit_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychosocial');
    }
};
