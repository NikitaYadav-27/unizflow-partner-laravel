<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FundRequest extends Model
{
    protected $table = 'fund_requests';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
    public $timestamps = true;
    
    
    public function getSuccessPendingRequests(){
        return $this->where("status","Success")->where("credited",0)->get();
    }
}