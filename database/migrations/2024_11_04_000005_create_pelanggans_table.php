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
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();

            $table->string('npwp', 25)->unique();
            $table->string('nitku', 25)->unique();
            $table->string('nama', 45);
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 15)->nullable();
            $table->string('email', 45)->unique()->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
