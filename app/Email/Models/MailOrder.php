<?php

namespace App\Email\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * Email邮件
 */
class MailOrder extends Model
{
	use SoftDeletes;

	protected $table = 'mail_order';
}