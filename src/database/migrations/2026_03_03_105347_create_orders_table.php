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
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // id: bigint unsigned（主キー）
            $table->id();

            // user_id: bigint unsigned（users.id 外部キー）
            $table->unsignedBigInteger('user_id');

            // item_id: bigint unsigned（items.id 外部キー）
            $table->unsignedBigInteger('item_id');

            // price: int unsigned
            $table->unsignedInteger('price');

            // payment_method: tinyint unsigned
            $table->unsignedTinyInteger('payment_method');

            // shipping_postal_code: varchar(20)
            $table->string('shipping_postal_code', 20);

            // shipping_address: varchar(255)
            $table->string('shipping_address');

            // shipping_building: varchar(255)
            $table->string('shipping_building')->nullable();

            // status: tinyint unsigned
            $table->unsignedTinyInteger('status');

            // created_at, updated_at
            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
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
