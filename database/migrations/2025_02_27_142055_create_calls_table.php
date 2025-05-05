<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id('call_id');
            $table->unsignedBigInteger('patient_id');
            $table->integer('call_result');
            $table->date('call_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
