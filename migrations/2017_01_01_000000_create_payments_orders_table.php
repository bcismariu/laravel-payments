<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('billable_type');
            $table->integer('billable_id');
            $table->integer('customer_id');
            $table->string('order_id');
            $table->integer('campaign_id');
            $table->integer('product_id');
            $table->decimal('amount', 5, 2);
            $table->string('status');
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
        Schema::dropIfExists('payments_orders');
    }
}
