<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_type_id');
            $table->string('category')->default('User'); // e.g., 'investment', 'rental', 'general'
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user to whom the notification is sent
            $table->unsignedBigInteger('member_id')->nullable(); // ID of the member related to the notification
            $table->unsignedBigInteger('customer_id')->nullable(); // ID of the member related to the notification
            $table->unsignedBigInteger('supplier_id')->nullable(); // ID of the member related to the notification
            $table->unsignedBigInteger('manufacturer_id')->nullable(); // ID of the member related to the notification
            $table->string('name')->nullable(); // Name of the notification
            $table->string('email')->nullable(); // Email address for sending notifications
            $table->string('phone')->nullable(); // Phone number for sending notifications
            $table->string('status')->default('Active'); // e.g., 'Pending', 'Sent', 'Failed'
            $table->boolean('daily')->default(false); // Daily notification
            $table->boolean('weekly')->default(false); // Weekly notification
            $table->boolean('monthly')->default(false); // Monthly notification
            $table->boolean('quarterly')->default(false); // Quarterly notification
            $table->boolean('yearly')->default(false); // Yearly notification
            $table->text('description')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
