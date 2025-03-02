<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('login_count')->default(0);
            $table->boolean('reset_required')->default(false);
            $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'))->unique()->after('id');
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_logout')->nullable();
            $table->boolean('auto_password_reset')->default(false);
            $table->string('password_reset_interval')->nullable();
            $table->string('last_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
