<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_number_formats', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->json('fields');
            $table->string('separator', 20)->default('/');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_number_formats');
    }
};
