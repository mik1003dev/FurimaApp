<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('item_images', function (Blueprint $table) {
            // id: bigint unsigned（主キー）
            $table->id();

            // item_id: bigint unsigned（items.id 外部キー）
            $table->unsignedBigInteger('item_id');

            // path: varchar(255)
            $table->string('path');

            // is_main: boolean
            $table->boolean('is_main');

            // created_at, updated_at: timestamp
            $table->timestamps();

            // 外部キー制約 item_id → items.id
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
        Schema::dropIfExists('item_images');
    }
}
