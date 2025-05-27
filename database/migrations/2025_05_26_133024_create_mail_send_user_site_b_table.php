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
        Schema::create('mail_send_user_site', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("site_id")->comment('站点ID');
            $table->bigInteger("send_user_id")->comment('对外发送邮件ID');
            
            $table->tinyInteger('mark')->default(1)->comment('状态:0无效,1有效,2待同步');
            $table->smallInteger('sort')->comment('排序')->default(125);
            $table->text('remarks')->nullable()->comment('备注');
            $table->timestamp("deleted_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_send_user_site_b');
    }
};
