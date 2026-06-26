<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add professional audit-trail fields + indexes to system_logs.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('user_agent')->nullable()->after('ip');
            $table->string('method')->nullable()->after('user_agent');     // HTTP verb
            $table->string('route')->nullable()->after('method');          // request path
            $table->string('entity_type')->nullable()->after('action');    // affected record type
            $table->string('entity_id')->nullable()->after('entity_type'); // affected record id
            $table->string('severity')->nullable()->after('http_code');    // INFO/WARNING/ERROR/CRITICAL
            $table->string('event_category')->nullable()->after('severity'); // AUTH/DATA/FINANCIAL/EXPORT/SYSTEM

            $table->index(['institution_id', 'created_at']);
            $table->index('action');
            $table->index('user_id');
            $table->index('http_code');
            $table->index('event_category');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndex(['institution_id', 'created_at']);
            $table->dropIndex(['action']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['http_code']);
            $table->dropIndex(['event_category']);
            $table->dropIndex(['severity']);
            $table->dropColumn([
                'user_agent', 'method', 'route', 'entity_type', 'entity_id', 'severity', 'event_category',
            ]);
        });
    }
};
