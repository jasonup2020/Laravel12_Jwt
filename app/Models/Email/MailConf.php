<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * Email配置
 */
class MailConf extends Model
{
	use SoftDeletes;

	protected $table = 'mail_conf';
        
        
        
}