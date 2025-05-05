<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diagnosis', function (Blueprint $table) {
            $table->id('diagnosis_id'); // This creates an auto-incrementing primary key
            $table->string('diagnosis_name');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diagnosis');
    }
};