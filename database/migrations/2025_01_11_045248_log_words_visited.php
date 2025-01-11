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

        Schema::create('logwords_visited', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('users_id')->nullable();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade'); // Relacionamento com 'users'
            $table->unsignedBigInteger('words_dictionary_id')->nullable(); 
            $table->foreign('words_dictionary_id')->references('id')->on('words_dictionary')->onDelete('cascade'); // Relacionamento com 'words_dictionary'
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('logwords_visited');

    }
    
};
