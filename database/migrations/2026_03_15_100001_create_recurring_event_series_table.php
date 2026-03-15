<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_event_series', function (Blueprint $table) {
            $table->id();

            $table->enum('recurrence_type', [
                'daily',
                'weekly',
                'monthly_date',
                'monthly_weekday',
            ]);

            // For 'weekly': 0=Sunday … 6=Saturday
            $table->unsignedTinyInteger('day_of_week')->nullable();

            // For 'monthly_weekday': which occurrence in the month (1–5)
            $table->unsignedTinyInteger('week_of_month')->nullable();

            // For 'monthly_weekday': 0=Sunday … 6=Saturday
            $table->unsignedTinyInteger('weekday')->nullable();

            $table->date('recurrence_end_date');

            // Denormalized count of total occurrences generated
            $table->unsignedSmallInteger('occurrence_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_event_series');
    }
};
