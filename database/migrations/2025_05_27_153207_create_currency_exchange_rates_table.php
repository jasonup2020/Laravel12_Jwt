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
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->comment('汇率表');
            $table->increments('id');
            $table->string('country')->default('')->comment('国家/地区名称');
            $table->string('currency_code')->default('')->comment('货币代码（ISO 4217标准，如USD、EUR）');
            $table->decimal('exchange_rate')->comment('汇率（1 USD 可兑换的目标货币数量）');
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
        Schema::dropIfExists('currency_exchange_rates');
    }
};
