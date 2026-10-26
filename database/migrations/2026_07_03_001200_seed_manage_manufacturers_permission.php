<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed the ManageManufacturers permission and grant it to every role so the
     * Manufacturers menu is visible to current users.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['permissions', 'role_permissions'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $permId = DB::table('permissions')->where('name', 'ManageManufacturers')->value('id');
        if (!$permId) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => 'ManageManufacturers',
                'status' => 'Active',
                'is_admin' => false,
                'description' => 'Manage manufacturers, contacts, ratings, contracts and documents',
                'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (DB::table('roles')->pluck('id') as $roleId) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $roleId)->where('permission_id', $permId)->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId, 'permission_id' => $permId,
                    'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        $permId = DB::table('permissions')->where('name', 'ManageManufacturers')->value('id');
        if ($permId) {
            DB::table('role_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
