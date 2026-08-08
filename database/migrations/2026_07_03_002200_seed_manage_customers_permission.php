<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed the ManageCustomers permission and grant it to every role so the
     * Customers menu is visible to current users.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['permissions', 'role_permissions'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $permId = DB::table('permissions')->where('name', 'ManageCustomers')->value('id');
        if (!$permId) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => 'ManageCustomers',
                'status' => 'Active',
                'is_admin' => false,
                'description' => 'Manage customers, groups, pricing, discounts and credit limits',
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
        $permId = DB::table('permissions')->where('name', 'ManageCustomers')->value('id');
        if ($permId) {
            DB::table('role_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
