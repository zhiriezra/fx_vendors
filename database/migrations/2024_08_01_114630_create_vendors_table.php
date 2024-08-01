<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('bvn')->nullable();
            $table->string('nin')->nullable();
            $table->enum('gender',['male','female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced'])->nullable();
            $table->string('current_location')->nullable();
            $table->string('permanent_address')->nullable();
            $table->foreignId('state_id')->nullable();
            $table->string('lga_id')->nullable();
            $table->string('community')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};
