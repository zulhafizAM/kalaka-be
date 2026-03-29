<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('speech', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->date('date');
            $table->string('kalaka_id', 50);
            $table->string('language');
            $table->string('mobile', 20)->nullable();
            $table->string('name');
            $table->string('origin');
            $table->string('recordfile')->nullable();
            $table->string('textgrid')->nullable();
            $table->string('rttm')->nullable();
            $table->longText('textcontent')->nullable();
            $table->integer('speakerno')->default(1);
            $table->integer('public')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speech');
    }
};
