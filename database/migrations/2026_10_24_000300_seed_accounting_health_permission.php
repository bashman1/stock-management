<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed the admin-only ManageAccountingHealth permission (scan a business's
     * books and apply repairs) and grant it to the roles that can already view
     * businesses, i.e. the system administrators.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['permissions', 'role_permissions'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $permId = DB::table('permissions')->where('name', 'ManageAccountingHealth')->value('id');
        if (!$permId) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => 'ManageAccountingHealth',
                'status' => 'Active',
                'is_admin' => true,
                'description' => 'Scan businesses for accounting issues and apply repairs',
                'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $adminPermId = DB::table('permissions')->where('name', 'CanViewInstitution')->value('id');
        $roleIds = DB::table('role_permissions')->where('permission_id', $adminPermId)->pluck('role_id');
        foreach ($roleIds as $roleId) {
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
        $permId = DB::table('permissions')->where('name', 'ManageAccountingHealth')->value('id');
        if ($permId) {
            DB::table('role_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
