<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * Email用户
 */
class MailUser extends Model
{
	use SoftDeletes;

	protected $table = 'mail_users';
}