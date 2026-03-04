<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            // id: bigint unsigned（主キー）
            $table->id();

            // user_id: bigint unsigned（users.id 外部キー）
            $table->unsignedBigInteger('user_id');

            // item_id: bigint unsigned（items.id 外部キー）
            $table->unsignedBigInteger('item_id');

            // body: text
            $table->text('body');

            // created_at, updated_at: timestamp
            $table->timestamps();

            // 外部キー制約 user_id → users.id
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

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
        Schema::dropIfExists('comments');
    }
}
