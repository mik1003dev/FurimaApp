<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('category_code');

            $table->timestamps();

            $table->unique(['item_id', 'category_code'], 'item_categories_unique');
            $table->index('item_id');
            $table->index('category_code');
        });
    }
}
