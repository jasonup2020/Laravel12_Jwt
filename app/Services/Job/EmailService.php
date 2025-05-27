<?php

namespace App\Services\Job;

use App\Services\BaseService;
//use Google\Client;
//use Google\Service\Gmail;
//use Microsoft\Graph\Graph;

use App\Models\Job\MailConfModel;
use App\Models\Job\MailUserModel;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan; // 新增 Artisan  调用 Artisan 命令，传递参数

class EmailService extends BaseService{
    public function __construct()
    {
        $this->model = new MailUserModel();
        
      
    }
}
