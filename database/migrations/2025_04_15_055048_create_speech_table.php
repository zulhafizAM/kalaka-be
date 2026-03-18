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
            $table->string('date');
            $table->string('kalaka_id');
            $table->string('language');
            $table->string('mobile');
            $table->string('name');
            $table->string('origin');
            $table->string('recordfile');
            $table->string('textgrid');
            $table->longText('textcontent');
            $table->integer('speakerno');
            $table->integer('public');
            $table->timestamps();
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
