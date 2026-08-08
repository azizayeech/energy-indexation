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
        Schema::create('consumptions', function (Blueprint $table) {
            $table->id();

            $table->date('date')->unique();

            $table->double('h1');
            $table->double('h2');
            $table->double('h3');
            $table->double('h4');
            $table->double('h5');
            $table->double('h6');
            $table->double('h7');
            $table->double('h8');
            $table->double('h9');
            $table->double('h10');
            $table->double('h11');
            $table->double('h12');
            $table->double('h13');
            $table->double('h14');
            $table->double('h15');
            $table->double('h16');
            $table->double('h17');
            $table->double('h18');
            $table->double('h19');
            $table->double('h20');
            $table->double('h21');
            $table->double('h22');
            $table->double('h23');
            $table->double('h24');
            $table->double('h25');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumptions');
    }
};
