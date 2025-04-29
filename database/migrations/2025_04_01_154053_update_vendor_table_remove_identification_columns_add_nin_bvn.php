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
        Schema::table('vendors', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['identification_mode', 'identification_no', 'community']);

            // Add new columns
            $table->string('nin')->nullable()->after('business_name'); 
            $table->string('bvn')->nullable()->after('nin'); 
            $table->string('country_id')->nullable()->after('permanent_address'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {

            $table->string('identification_mode')->nullable();
            $table->string('identification_no')->nullable();
            $table->string('community')->nullable();

            $table->dropColumn(['nin', 'bvn', 'country']);
        });
    }
};
