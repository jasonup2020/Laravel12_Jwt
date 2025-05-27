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
        Schema::create('mail_users', function (Blueprint $table) {
            $table->comment('用户邮件基础信息');
            $table->id();
            
            $table->string('name')->comment('用户名');
            $table->string('email')->comment('Email用户');
            $table->string('email_config_id')->comment('Email配置ID');
            $table->string('passwords')->comment('Email用户密码');
            
            $table->string('privated_user')->nullable()->default("")->comment('Email_API专业用户');
            $table->string('privated_code')->nullable()->default("")->comment('Email_API专业密码');
            
            $table->string('phone')->nullable()->comment('验证手机号');
            $table->string('orther_mail')->nullable()->default("")->comment('辅助邮箱');
            
            $table->string('bay_user')->nullable()->default("")->comment('购买人');
            $table->string('old_phone')->nullable()->default("")->comment('历史手机号');
            $table->string('old_orther_mail')->nullable()->default("")->comment('历史辅助邮箱');
            $table->string('latest_mail_uid')->default(0)->comment('最新一封邮件的UID');
            
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
        Schema::dropIfExists('mail_users');
    }
};
