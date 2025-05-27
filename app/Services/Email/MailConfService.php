<?php

namespace App\Services\Email;

use App\Models\Email\MailConf;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * Email配置
 *
 * @method MailConf getModel()
 * @method MailConf|\Illuminate\Database\Query\Builder query()
 */
class MailConfService extends AdminService
{
	protected string $modelName = MailConf::class;
        
        public static function getEmailConf() {
            $m_mc=new MailConf();
            $d_mc=$m_mc->where([["mark","=",1]])->orderby("sort","desc")->get(["id","server_name","server_type","name"]);
            
            $r_mc=[];
            foreach ($d_mc as $k => $v) {
                $r_mc[]=["label"=>$v["server_name"]." ". $v["server_type"]." ". $v["name"],"value"=>$v["id"],];
            }
            return $r_mc;
        }
}