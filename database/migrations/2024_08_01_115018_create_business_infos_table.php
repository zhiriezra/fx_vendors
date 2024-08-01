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
        Schema::create('business_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('registration_number')->nullable();
            $table->enum('type',['Sole proprietorship', 'Partnership', 'Limited liability'])->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('bank')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('tin')->nullable();
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
        Schema::dropIfExists('business_infos');
    }
};
