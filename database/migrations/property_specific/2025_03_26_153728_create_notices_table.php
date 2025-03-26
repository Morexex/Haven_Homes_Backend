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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Notice title
            $table->text('message'); // Detailed notice message
            $table->enum('type', ['general', 'complaint', 'payment', 'maintenance', 'vacancy', 'audit']); // Notice category
            $table->unsignedBigInteger('source_id')->nullable(); // ID of the related operation
            $table->string('source_type')->nullable(); // Model type (e.g., Complaint, Payment, Maintenance)
            $table->unsignedBigInteger('user_id'); // Who created the notice
            $table->timestamp('published_at')->nullable(); // Optional publish date
            $table->timestamp('expires_at')->nullable(); // Expiration date
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
