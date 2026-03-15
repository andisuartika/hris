<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedule_days', function (Blueprint $table) {

            $table->id();

            $table->foreignId('work_schedule_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->tinyInteger('day_of_week');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            $table->integer('work_hours')->default(8);

            $table->integer('tolerance_late')->default(0);
            $table->integer('tolerance_early_leave')->default(0);

            $table->boolean('is_working_day')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedule_days');
    }
};
