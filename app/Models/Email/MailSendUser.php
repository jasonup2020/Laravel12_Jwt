<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * Email发送邮件用户
 */
class MailSendUser extends Model
{
	use SoftDeletes;

	protected $table = 'mail_send_user';
}