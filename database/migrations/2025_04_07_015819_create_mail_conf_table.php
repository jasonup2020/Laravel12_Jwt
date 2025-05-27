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
        Schema::create('mail_conf', function (Blueprint $table) {
            $table->comment('邮件服务配置信息');
            $table->id();
            
            $table->string('server_name')->default("abc@gmail.com")->comment('邮件服务名[abc@gmail.com]');
            $table->string('server_type')->default("smtp")->comment('邮件类型:IMTPimap,POPpop,SMTPsmtp');
            $table->string('name')->nullable()->comment('Server Name');
            $table->string('server')->nullable()->comment('Server');
            $table->string('port')->default("993")->comment('Port:993,995,587');
            $table->string('encryption')->default("SSL/TLS")->comment('Encryption');
            $table->string('authentication_method')->nullable()->comment('Authentication Method');
            $table->tinyInteger('imap_mark')->default(1)->comment('状态:0无效,1有效');

            $table->tinyInteger('mark')->default(1)->comment('状态:0无效,1有效');
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
        Schema::dropIfExists('mail_conf');
    }
};
