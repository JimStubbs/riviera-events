<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organizer_name', 150);
            $table->string('contact_email', 255);
            $table->unsignedInteger('amount')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['inquiry', 'active', 'closed'])->default('inquiry');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorships');
    }
};
