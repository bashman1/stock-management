<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1) Extend the existing fixed_assets table
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('ref_no')->nullable()->after('id');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('expense_account');
            $table->string('payment_method')->nullable()->after('purchase_amount');     // CASH|BANK|CREDIT
            $table->string('pay_acct_no')->nullable()->after('payment_method');
            $table->string('depreciation_method')->default('STRAIGHT_LINE')->after('depreciation_rate_id'); // STRAIGHT_LINE|REDUCING_BALANCE
            $table->double('salvage_value', 14, 2)->default(0)->after('purchase_amount');
            $table->double('accumulated_depreciation', 14, 2)->default(0)->after('salvage_value');
            $table->double('book_value', 14, 2)->nullable()->after('accumulated_depreciation');
            $table->date('last_depreciation_date')->nullable()->after('book_value');
            $table->string('location')->nullable()->after('branch_id');                  // room/dept; branch_id = location branch
            $table->string('tran_id')->nullable()->after('location');                    // purchase journal
        });
        // legacy NOT NULL columns must allow nulls now (we drive GL via payment_method etc.)
        foreach (['payment_method_id', 'duration_id', 'depreciation_rate_id'] as $col) {
            DB::statement("ALTER TABLE fixed_assets ALTER COLUMN $col DROP NOT NULL");
        }

        // 2) Asset transfers (location/branch history, no GL)
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->timestamp('transfer_date')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'institution_id']);
        });

        // 3) Asset maintenance (logged + posted as a GL expense)
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('ref_no')->nullable();
            $table->timestamp('maintenance_date')->nullable();
            $table->string('type')->nullable();                 // Repair | Service | Inspection
            $table->text('description')->nullable();
            $table->double('cost', 14, 2)->default(0);
            $table->string('payee')->nullable();
            $table->string('expense_acct_no')->nullable();
            $table->string('payment_method')->nullable();       // CASH|BANK|CREDIT
            $table->string('pay_acct_no')->nullable();
            $table->string('tran_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'institution_id']);
        });

        // 4) Asset depreciation schedule/history (also the idempotency guard)
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('period');                           // YYYY-MM
            $table->string('method')->nullable();
            $table->double('opening_book_value', 14, 2)->default(0);
            $table->double('depreciation_amount', 14, 2)->default(0);
            $table->double('accumulated_after', 14, 2)->default(0);
            $table->double('closing_book_value', 14, 2)->default(0);
            $table->timestamp('run_date')->nullable();
            $table->string('tran_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->unique(['asset_id', 'period']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_transfers');
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn([
                'ref_no', 'supplier_id', 'payment_method', 'pay_acct_no', 'depreciation_method',
                'salvage_value', 'accumulated_depreciation', 'book_value', 'last_depreciation_date',
                'location', 'tran_id',
            ]);
        });
    }
};
