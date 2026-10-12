<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class IP extends Model
{
    protected $table = 'whitelisted_ips';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
    
    public function isWhiteListed($user_id,$ip){
       return $this->where("user_id",$user_id)->where("ip",$ip)->where("deleted",0)->count() > 0;
    }
    

}
