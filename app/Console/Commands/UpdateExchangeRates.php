<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRates extends Command
{
    protected $signature = 'cron:update_exchange_rates';
    protected $description = 'php artisan cron:update_exchange_rates    【每日自动更新汇率表（以USD为基准）】';

    public function handle()
    {
        // 从 .env 获取 API Key（需提前在 .env 配置 EXCHANGE_API_KEY=your_api_key）
        // $apiKey = config('services.exchange.api_key');
        $apiKey = "7F5kBObgRPq3m8c5RtKMmPF6xBtywdc5";
        $baseCurrency = 'USD'; // 基准货币（USD）

        // 调用 API 获取实时汇率（示例使用 apilayer 的 exchangeratesapi）  每月免费100次
        $response = Http::get('https://api.apilayer.com/exchangerates_data/latest', [
            'base' => $baseCurrency,
            'apikey' => $apiKey,
            
        ]);

        // 检查 API 响应是否成功
        if (!$response->successful()) {
            $this->error('API 请求失败：' . $response->body());
            return 1;
        }

        $rates = $response->json()['rates'] ?? [];
        if (empty($rates)) {
            $this->error('未获取到有效汇率数据');
            return 1;
        }


        // 更新数据库（假设表名为 currency_exchange_rates）
        $updatedCount = 0;
        foreach ($rates as $currencyCode => $rate) {
            // 仅更新预定义的货币（可选过滤，避免全量更新）
            $updated = DB::table('currency_exchange_rates')
                ->where('currency_code', $currencyCode)
                ->update([
                    'exchange_rate' => $rate,
                    'updated_at' => Carbon::now(),
                ]);
            if ($updated) {
                $updatedCount++;
            }
        }

        Log::info("UpdateExchangeRates->handle()",["update"=>$updatedCount,"rates"=>$rates]);
        $this->info("成功更新 {$updatedCount} 条汇率数据");
        return 0;
    }
}