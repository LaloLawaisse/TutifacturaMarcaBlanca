<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('quantity', 20, 4)->default(1);
            $table->timestamps();

            $table->index(['business_id', 'product_id']);
            $table->index(['business_id', 'material_id']);
            $table->unique(['business_id', 'product_id', 'material_id'], 'material_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_product');
    }
};

