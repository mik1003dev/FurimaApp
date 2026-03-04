<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            // id: bigint unsigned（主キー）
            $table->id();

            // user_id: bigint unsigned（users.id 外部キー）
            $table->unsignedBigInteger('user_id');

            // name: varchar(255)
            $table->string('name');

            // brand: varchar(255)
            $table->string('brand')->nullable();

            // description: text
            $table->text('description');

            // price: int unsigned
            $table->unsignedInteger('price');

            // category: tinyint unsigned
            $table->unsignedTinyInteger('category');

            // condition: tinyint unsigned
            $table->unsignedTinyInteger('condition');

            // is_sold: boolean
            $table->boolean('is_sold');

            // created_at, updated_at: timestamp
            $table->timestamps();

            // 外部キー制約 user_id → users.id
            $table->foreign('user_id')
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
        Schema::dropIfExists('items');
    }
}
