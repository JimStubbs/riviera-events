<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enums as text and doesn't support MODIFY COLUMN — skip
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE recurring_event_series MODIFY COLUMN recurrence_type ENUM('daily','weekly','biweekly','monthly_date','monthly_weekday') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE recurring_event_series MODIFY COLUMN recurrence_type ENUM('daily','weekly','monthly_date','monthly_weekday') NOT NULL");
        }
    }
};
