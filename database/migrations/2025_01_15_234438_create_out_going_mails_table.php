<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_going_mails', function (Blueprint $table) {
            $table->id();
            $table->text("subject");
            $table->text("body");
            $table->json("to");
            $table->json("cc")->nullable();
            $table->json("bcc")->nullable();
            $table->text("attachment")->nullable();
            $table->boolean("has_attachment")->default(false);
            $table->boolean("sent")->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text("status")->default("Active");
            $table->text("failure_reason")->nullable();
            $table->text("attachment_name")->nullable();
            $table->unsignedBigInteger("failure_count")->default(0);
            $table->unsignedBigInteger("priority")->default(0);
            $table->text("type")->default("EMAIL");
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('retry_after')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('out_going_mails');
    }
};
