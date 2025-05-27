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
        Schema::create('email_risk_levels', function (Blueprint $table) {
            $table->comment("风险配置提取表");
            $table->id();
            $table->string('cn_name')->comment('特征标记码用于标识风险特征,如垃圾邮件关键词,钓鱼链接等');
            $table->string('risk_level')->comment('风险等级:1低 2中 3高');
            $table->enum('source', ['title', 'content'])->default("title")->comment('特征提取来源:title邮件标题 content邮件正文');
            $table->string('feature_code')->comment('特征标记代码');
            
            $table->smallInteger('sort')->default("125")->comment('显示顺序');
            $table->tinyInteger('mark')->default("1")->comment('有效标识:1正常 0删除');
            $table->text('remarks')->nullable()->comment('备注说明');
            $table->timestamp("deleted_at")->nullable();
            $table->timestamps();
            
            // 可选：添加索引优化查询（根据业务需求）
            $table->index(['risk_level', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_risk_levels');
    }
};
