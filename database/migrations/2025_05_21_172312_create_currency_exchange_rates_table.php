<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->comment("汇率表");
            $table->id();
            $table->string('country', 50)->comment('国家/地区名称');
            $table->string('currency_code', 3)->comment('货币代码（ISO 4217标准，如USD、EUR）');
            $table->decimal('exchange_rate', 10, 6)->comment('汇率（1 USD 可兑换的目标货币数量）');
//            $table->timestamp('updated_at')->comment('汇率更新时间');
            $table->timestamp("deleted_at")->nullable();
            $table->timestamps();
        });
        
        self::run_add();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
    
    
    public function run_add() {
        $data_time=Carbon::now();
        $rates = [
            ['country' => '美国', 'currency_code' => 'USD', 'exchange_rate' => 1.000000, 'updated_at' => $data_time],
            ['country' => '欧元区', 'currency_code' => 'EUR', 'exchange_rate' => 0.925000, 'updated_at' => $data_time],
            ['country' => '中国', 'currency_code' => 'CNY', 'exchange_rate' => 7.250000, 'updated_at' => $data_time],
            ['country' => '中国香港', 'currency_code' => 'HKD', 'exchange_rate' => 7.850000, 'updated_at' => $data_time],
            ['country' => '中国台湾', 'currency_code' => 'TWD', 'exchange_rate' => 30.500000, 'updated_at' => $data_time],
            ['country' => '日本', 'currency_code' => 'JPY', 'exchange_rate' => 150.500000, 'updated_at' => $data_time],
            ['country' => '英国', 'currency_code' => 'GBP', 'exchange_rate' => 0.785000, 'updated_at' => $data_time],
            ['country' => '加拿大', 'currency_code' => 'CAD', 'exchange_rate' => 1.350000, 'updated_at' => $data_time],
            ['country' => '澳大利亚', 'currency_code' => 'AUD', 'exchange_rate' => 1.530000, 'updated_at' => $data_time],
            ['country' => '瑞士', 'currency_code' => 'CHF', 'exchange_rate' => 0.905000, 'updated_at' => $data_time],
            ['country' => '韩国', 'currency_code' => 'KRW', 'exchange_rate' => 1340.000000, 'updated_at' => $data_time],
            ['country' => '印度', 'currency_code' => 'INR', 'exchange_rate' => 83.500000, 'updated_at' => $data_time],
            ['country' => '巴西', 'currency_code' => 'BRL', 'exchange_rate' => 4.950000, 'updated_at' => $data_time],
            ['country' => '墨西哥', 'currency_code' => 'MXN', 'exchange_rate' => 17.000000, 'updated_at' => $data_time],
            ['country' => '俄罗斯', 'currency_code' => 'RUB', 'exchange_rate' => 98.000000, 'updated_at' => $data_time],
            ['country' => '南非', 'currency_code' => 'ZAR', 'exchange_rate' => 18.500000, 'updated_at' => $data_time],
            ['country' => '新加坡', 'currency_code' => 'SGD', 'exchange_rate' => 1.340000, 'updated_at' => $data_time],
            ['country' => '挪威', 'currency_code' => 'NOK', 'exchange_rate' => 10.500000, 'updated_at' => $data_time],
            ['country' => '瑞典', 'currency_code' => 'SEK', 'exchange_rate' => 10.700000, 'updated_at' => $data_time],
            ['country' => '丹麦', 'currency_code' => 'DKK', 'exchange_rate' => 6.900000, 'updated_at' => $data_time],
            ['country' => '新西兰', 'currency_code' => 'NZD', 'exchange_rate' => 1.620000, 'updated_at' => $data_time],
            ['country' => '土耳其', 'currency_code' => 'TRY', 'exchange_rate' => 28.000000, 'updated_at' => $data_time],
            
            // 中东前10国家
            ['country' => '沙特阿拉伯', 'currency_code' => 'SAR', 'exchange_rate' => 3.750000, 'updated_at' =>$data_time],
            ['country' => '阿拉伯联合酋长国', 'currency_code' => 'AED', 'exchange_rate' => 3.670000, 'updated_at' =>$data_time],
//            ['country' => '伊朗', 'currency_code' => 'IRR', 'exchange_rate' => 42000.000000, 'updated_at' =>$data_time],
            ['country' => '以色列', 'currency_code' => 'ILS', 'exchange_rate' => 3.850000, 'updated_at' =>$data_time],
//            ['country' => '伊拉克', 'currency_code' => 'IQD', 'exchange_rate' => 1320.000000, 'updated_at' =>$data_time],
            ['country' => '卡塔尔', 'currency_code' => 'QAR', 'exchange_rate' => 3.640000, 'updated_at' =>$data_time],
            ['country' => '科威特', 'currency_code' => 'KWD', 'exchange_rate' => 0.300000, 'updated_at' =>$data_time],
            ['country' => '阿曼', 'currency_code' => 'OMR', 'exchange_rate' => 0.385000, 'updated_at' =>$data_time],
            ['country' => '巴林', 'currency_code' => 'BHD', 'exchange_rate' => 0.377000, 'updated_at' =>$data_time],
            ['country' => '约旦', 'currency_code' => 'JOD', 'exchange_rate' => 0.709000, 'updated_at' =>$data_time],
        ];

        DB::table('currency_exchange_rates')->insert($rates);
    }
};
