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
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('tipo_recurso_id')->nullable()->constrained('tipo_recursos')->onDelete('set null');
        });

        // Migrate existing types
        $tipos = \Illuminate\Support\Facades\DB::table('categorias')->select('tipo')->distinct()->pluck('tipo');
        foreach($tipos as $tipo) {
            if($tipo) {
                $recursoId = \Illuminate\Support\Facades\DB::table('tipo_recursos')->insertGetId([
                    'nombre' => $tipo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('categorias')->where('tipo', $tipo)->update(['tipo_recurso_id' => $recursoId]);
            }
        }

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('tipo')->nullable();
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['tipo_recurso_id']);
            $table->dropColumn('tipo_recurso_id');
        });
    }
};
