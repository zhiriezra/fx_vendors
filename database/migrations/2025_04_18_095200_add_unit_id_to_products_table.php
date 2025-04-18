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
        Schema::table('products', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['unit']);

            // Add new columns
            $table->unsignedBigInteger('unit_id')->nullable()->after('name');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->nullable();

            $table->dropColumn(['unit_id']);
            $table->dropForeign(['unit_id']);
        });
    }
};
