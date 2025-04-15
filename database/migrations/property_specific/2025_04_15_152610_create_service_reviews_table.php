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
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->id();
            $table->boolean('satisfied')->nullable(); // Yes/No
            $table->text('feedback')->nullable();     // Why satisfied/what went wrong
            $table->float('rating', 2, 1)->default(0); // e.g. 4.5
            // complaint_id to refference the complaint
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reviews');
    }
};
