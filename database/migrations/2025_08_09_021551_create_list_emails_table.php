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
        Schema::create('list_emails', function (Blueprint $table) {
            $table->id(); // Kolom ID standar
            $table->string('name'); // Nama penerima, contoh: "Direktur Produksi"
            $table->string('email')->unique(); // Alamat email, harus unik (tidak boleh ada duplikat)
            $table->boolean('is_active')->default(true); // Status, untuk menonaktifkan tanpa menghapus
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_emails');
    }
};
