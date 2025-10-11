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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('national_id')->nullable()->after('bvn');
            $table->string('kra_pin')->nullable()->after('national_id');
            $table->string('secondary_phone')->nullable()->after('kra_pin');
            $table->string('ward_id')->nullable()->after('secondary_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['national_id', 'kra_pin', 'secondary_phone', 'ward']);
        });
    }
};
