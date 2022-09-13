<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('status',['Pending', 'Approved', 'Dispatched', 'Delivered', 'Completed'])->default('Pending');
            $table->string('transaction_code')->comment('Payment Code')->nullable();
            $table->float('total_amount');
            $table->float('shipment_fee')->default(0);
            $table->float('paid_amount')->default(0);
            $table->timestamp('order_date')->useCurrent();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('delivery_address');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('driver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
