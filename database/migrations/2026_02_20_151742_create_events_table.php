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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('image')->nullable();
            $table->string('organizer', 150)->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedTinyInteger('featured_order')->default(0);
            $table->enum('status', [
                'draft',
                'pending_verification',
                'pending_payment',
                'pending_approval',
                'approved',
                'rejected',
            ])->default('draft');
            $table->boolean('is_paid')->default(false);
            $table->string('stripe_payment_id', 100)->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->string('submitter_email')->nullable();
            $table->string('verification_token', 64)->nullable()->unique();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_premium');
            $table->index('is_featured');
            $table->index(['status', 'start_date']);
            $table->index('verification_token');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
