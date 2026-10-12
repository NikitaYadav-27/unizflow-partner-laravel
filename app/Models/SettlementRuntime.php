<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class SettlementRuntime extends Model
{
    protected $table = 'settlement_runtimes';
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

    

}
