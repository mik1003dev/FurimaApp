<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProfileCompletedAtToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('profile_completed_at')->nullable()->after('avatar_path');
        });

        DB::table('users')
            ->whereNotNull('avatar_path')
            ->whereNotNull('postal_code')
            ->whereNotNull('address')
            ->update([
                'profile_completed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_completed_at');
        });
    }
}
