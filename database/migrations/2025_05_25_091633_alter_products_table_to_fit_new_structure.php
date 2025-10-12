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
            // Check if columns exist before dropping them
            if (Schema::hasColumn('products', 'manufacturer')) {
                $table->dropColumn('manufacturer');
            }
            if (Schema::hasColumn('products', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
            
            // Check if manufacturer_product_id already exists before adding it
            if (!Schema::hasColumn('products', 'manufacturer_product_id')) {
                $table->foreignId('manufacturer_product_id')->after('vendor_id')->constrained('manufacturer_products');
            }

            // $table->dropColumn('category_id'); remove manually
            // $table->dropColumn('sub_category_id'); remove manually
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
            $table->string('manufacturer');
            $table->string('name');
            $table->string('description');
            $table->dropColumn('manufacturer_product_id');
            // $table->foreignId('category_id')->constrained('categories');
            // $table->foreignId('sub_category_id')->constrained('sub_categories');
        });
    }
};
