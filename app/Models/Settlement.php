<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Settlement extends Model
{
    protected $table = 'settlements';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
    public function getRecent($user_id, $limit) {
        
        return $this->select(['txnid', 'status','net_amount','settle_to','created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }
    
    public function getSuccessPendingCredit(){
        return $this->where("status","Success")->where("credited",0)->where('settle_to','wallet')->get();
    }

    

}
