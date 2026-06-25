<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expenses register: each row is the source of truth for an expense and is
     * linked (via tran_id) to a balanced GL journal (Dr expense / Cr cash|bank|AP).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();
            $table->string('expense_acct_no');                 // the EXPENSE GL account (category)
            $table->string('category_name')->nullable();       // snapshot of the account description
            $table->double('amount', 12, 2)->default(0);
            $table->string('payment_method');                  // CASH | BANK | CREDIT
            $table->string('pay_acct_no')->nullable();         // credited account (cash/bank/AP)
            $table->string('payee')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('expense_date')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('tran_id')->nullable();             // GL journal of the expense
            $table->string('payment_status')->default('Paid'); // Paid | Unpaid (credit)
            $table->string('paid_tran_id')->nullable();        // GL journal of the settlement
            $table->timestamp('paid_on')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
