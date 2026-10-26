<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supplier profile satellites: contacts, multi-criteria ratings, contracts and
     * generic documents. All scoped to a supplier (and institution/branch).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index('supplier_id');
        });

        Schema::create('supplier_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->double('quality')->default(0);       // 1-5
            $table->double('delivery')->default(0);      // 1-5
            $table->double('price')->default(0);         // 1-5
            $table->double('communication')->default(0); // 1-5
            $table->double('overall')->default(0);       // avg of the four
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('rated_by')->nullable();
            $table->timestamp('rating_date')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index('supplier_id');
        });

        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('title');
            $table->string('contract_no')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->double('value')->default(0);
            $table->string('status')->default('Active'); // Active|Expired|Terminated
            $table->text('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index('supplier_id');
        });

        Schema::create('supplier_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('name');
            $table->string('type')->nullable(); // License|Certificate|Insurance|Other
            $table->text('file_path')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_documents');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('supplier_ratings');
        Schema::dropIfExists('supplier_contacts');
    }
};
