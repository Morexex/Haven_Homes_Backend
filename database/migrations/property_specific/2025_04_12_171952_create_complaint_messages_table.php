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
        Schema::create('complaint_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender');
            $table->text('message');
            $table->string('attachment_url')->nullable();
            $table->timestamps();
        
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('property_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_messages');
    }
};
