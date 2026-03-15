<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->date('holiday_date');

            $table->enum('type', [
                'national',
                'religious',
                'company',
                'special'
            ])->default('national');

            $table->text('description')->nullable();

            $table->boolean('is_generated')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
