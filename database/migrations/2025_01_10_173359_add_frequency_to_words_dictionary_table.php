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
        Schema::table('words_dictionary', function (Blueprint $table) {

            $table->integer('frequency')->default(1)->after('words');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('words_dictionary', function (Blueprint $table) {

            $table->dropColumn('frequency');
            
        });
    }

};
