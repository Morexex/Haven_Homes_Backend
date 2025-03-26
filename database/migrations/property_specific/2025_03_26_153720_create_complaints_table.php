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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('category', 50);
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent']);
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected']);
            $table->unsignedBigInteger('complainant_id');
            $table->dateTime('incident_date');
            $table->text('evidence_url')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->foreign('complainant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
