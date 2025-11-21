<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->unsignedTinyInteger('day_of_month');
            $table->date('next_run_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'active']);
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_costs');
    }
};
