
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_number_sequences', function (Blueprint $table) {
            $table->id();

            // Tahun penerbitan sertifikat.
            $table->unsignedSmallInteger('year')->unique();

            // Nomor urut terakhir yang sudah diterbitkan.
            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_number_sequences');
    }
};
