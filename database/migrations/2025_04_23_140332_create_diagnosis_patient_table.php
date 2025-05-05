<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create('patient_diagnosis', function (Blueprint $table) {
            $table->string('patient_id');
            $table->string('diagnosis_id');
            $table->timestamps();
            
            $table->primary(['patient_id', 'diagnosis_id']);
          
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_diagnosis');
    }
};