<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('recurring_series_id')
                ->nullable()
                ->after('rejection_reason')
                ->constrained('recurring_event_series')
                ->nullOnDelete();

            $table->index('recurring_series_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['recurring_series_id']);
            $table->dropIndex(['recurring_series_id']);
            $table->dropColumn('recurring_series_id');
        });
    }
};
