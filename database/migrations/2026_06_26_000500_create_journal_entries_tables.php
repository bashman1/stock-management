<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();
            $table->timestamp('journal_date')->nullable();
            $table->text('narration')->nullable();
            $table->double('total_amount', 14, 2)->default(0);
            $table->string('tran_id')->nullable();
            $table->string('status')->default('Active');     // Active | Reversed
            $table->string('reversed_tran_id')->nullable();
            $table->timestamp('reversed_on')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'branch_id', 'status']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->string('acct_no');
            $table->string('acct_type')->nullable();
            $table->string('dr_cr');                          // Dr | Cr
            $table->double('amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index('journal_entry_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
