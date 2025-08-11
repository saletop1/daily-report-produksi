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
    Schema::table('list_emails', function (Blueprint $table) {
        // Tambahkan kolom role setelah kolom 'email'
        $table->string('role')->default('staff')->after('email');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('list_emails', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
