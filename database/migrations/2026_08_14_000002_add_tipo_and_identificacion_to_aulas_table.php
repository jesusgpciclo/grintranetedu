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
         Schema::table('aulas', function (Blueprint $table) {
             $table->string('tipo')->default('aula')->after('nombre'); // 'aula' o 'zona'
             $table->string('identificacion')->nullable()->after('tipo');
             $table->integer('capacidad')->nullable()->change();
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('aulas', function (Blueprint $table) {
             $table->dropColumn(['tipo', 'identificacion']);
             $table->integer('capacidad')->default(30)->change();
         });
     }
};
