<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication', function (Blueprint $table) {
            $table->id('medication_id');
            $table->text('item_name')->nullable();
            $table->text('composition')->nullable();
            $table->text('brand')->nullable();
            $table->text('formulation')->nullable();
            $table->text('category')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication');
    }
};
