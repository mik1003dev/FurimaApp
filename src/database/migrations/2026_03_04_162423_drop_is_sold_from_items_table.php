<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIsSoldFromItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('is_sold');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_sold')->default(false);
        });
    }
}
