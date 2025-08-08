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
        Schema::create('sap_yppr009_data', function (Blueprint $table) {
            $table->id();
            $table->string('LGORT')->nullable();
            $table->string('DISPO')->nullable();
            $table->string('AUFNR')->nullable();
            $table->string('CHARG')->nullable();
            $table->string('MATNR')->nullable();
            $table->string('MAKTX')->nullable();
            $table->string('MAT_KDAUF')->nullable();
            $table->string('MAT_KDPOS')->nullable();
            $table->decimal('PSMNG', 15, 3)->default(0);
            $table->decimal('MENGE', 15, 3)->default(0);
            $table->decimal('MENGEX', 15, 3)->default(0);
            $table->decimal('WEMNG', 15, 3)->default(0);
            $table->string('MEINS')->nullable();
            $table->date('BUDAT_MKPF')->nullable();
            $table->integer('NODAY')->default(0);
            $table->decimal('NETPR', 15, 3)->default(0);
            $table->decimal('VALUS', 15, 3)->default(0);
            $table->decimal('VALUSX', 15, 3)->default(0);
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index('BUDAT_MKPF');
            $table->index('DISPO');
            $table->index('MATNR');
            $table->index(['BUDAT_MKPF', 'DISPO']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_yppr009_data');
    }
};
