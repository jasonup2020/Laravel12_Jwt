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
        Schema::create('mail_order', function (Blueprint $table) {
            $table->comment('邮件信息');
            
            $table->id();
            
            $table->string('email_title')->comment('Email标题');
            $table->string('email_to')->comment('Email发件人');
            $table->string('email_cc')->comment('Email收件人');
            $table->string('email_type')->nullable()->comment('Email类型');
            $table->string('email_content')->nullable()->comment('Email内容');
            $table->string('email_time')->nullable()->comment('Email时间');
            
            $table->string('extract_money')->nullable()->comment('Email里提取到的钱');
            $table->string('extract_email')->nullable()->comment('Email里提取到的Email');
            $table->string('extract_orders')->nullable()->comment('Email里提取到订单号');
            // $table->string('extract_type')->comment('Email里提取到状态');
            
            $table->tinyInteger('mark')->default("1")->comment('有效标识:1正常 0删除');
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
        Schema::dropIfExists('mail_order');
    }
};
