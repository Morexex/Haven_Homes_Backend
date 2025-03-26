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
        Schema::create('bulk_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Who created the communication
            $table->string('type'); // 'whatsapp', 'email', 'sms'
            $table->string('status')->default('pending'); // 'pending', 'sent', 'failed'
            $table->timestamp('scheduled_at')->nullable(); // If it's scheduled
            $table->timestamps();
        });

        Schema::create('bulk_communication_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_communication_id')->constrained('bulk_communications')->onDelete('cascade'); // Parent communication
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Recipient
            $table->text('message'); // Custom message per recipient
            $table->string('status')->default('pending'); // 'pending', 'sent', 'failed'
            $table->text('failure_reason')->nullable(); // Store failure reasons, if any
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_communications');
        Schema::dropIfExists('bulk_communication_recipients');
    }
};
