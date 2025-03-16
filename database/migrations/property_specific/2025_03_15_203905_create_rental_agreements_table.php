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
        Schema::create('rental_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade'); // Foreign key to rooms
            $table->date('payment_date');
            $table->date('tenancy_start_date');

            // Tenant details
            $table->string('tenant_name');
            $table->string('tenant_email');
            $table->string('tenant_phone');

            // Agreement status
            $table->enum('room_agreement', ['yes', 'no']);

            // Charges with their agreement status stored together
            $table->json('charges_agreement')->nullable();

            // Amenities agreement
            $table->json('amenities_agreement')->nullable();

            // ID files
            $table->string('id_front')->nullable();
            $table->string('id_back')->nullable();

            $table->foreignId('property_code')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_agreements');
    }
};
