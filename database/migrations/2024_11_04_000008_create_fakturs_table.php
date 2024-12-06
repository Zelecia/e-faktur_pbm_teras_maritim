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
        Schema::create('fakturs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tipe_faktur_id')->constrained('tipe_fakturs', 'id');
            $table->foreignId('pelanggan_id')->constrained('pelanggans', 'id');
            $table->foreignId('penandatangan_id')->constrained('penandatangans', 'id');
            $table->foreignId('referensi_id')->constrained('barang_jasas', 'id');

            $table->string('nomor', 30)->unique();
            $table->date('tanggal');
            $table->unsignedSmallInteger('masa')->length(3);
            $table->year('tahun');
            $table->unsignedBigInteger('dpp');
            $table->unsignedTinyInteger('ppn')->length(2);
            $table->unsignedTinyInteger('status')->length(1);
            $table->dateTime('tanggal_approval');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fakturs');
    }
};
