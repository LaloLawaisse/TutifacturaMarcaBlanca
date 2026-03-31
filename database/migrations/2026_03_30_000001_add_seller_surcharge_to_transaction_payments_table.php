<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerSurchargeToTransactionPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->decimal('seller_surcharge', 22, 4)->default(0)->after('note')
                  ->comment('Monto del recargo absorbido por el vendedor (no cargado al cliente)');
        });
    }

    public function down()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->dropColumn('seller_surcharge');
        });
    }
}
