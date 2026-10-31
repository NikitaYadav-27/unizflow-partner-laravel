<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class ApiRequest extends Model
{
    protected $table = 'api_requests';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
    public function findUser($user_id){
        return $this->where("user_id", $user_id)->first();
    }
    
    public function isReqIdExists($reqId) {
        return $this->where('reqid', $reqId)->exists();
    }

}
