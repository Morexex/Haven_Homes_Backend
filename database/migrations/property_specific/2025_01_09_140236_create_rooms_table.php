<?php  

use Illuminate\Database\Migrations\Migration;  
use Illuminate\Database\Schema\Blueprint;  
use Illuminate\Support\Facades\Schema;  

class CreateRoomsTable extends Migration  
{  
    /**  
     * Run the migrations.  
     *  
     * @return void  
     */  
    public function up()  
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('is_vacant')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('room_categories')->onDelete('cascade'); // Linked to category
            $table->integer('quantity')->default(1);
            $table->string('floor')->nullable();
            $table->timestamps();
        });  
    }  

    /**  
     * Reverse the migrations.  
     *  
     * @return void  
     */  
    public function down()  
    {  
        Schema::dropIfExists('rooms');  
    }  
}