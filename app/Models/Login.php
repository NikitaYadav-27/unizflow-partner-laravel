<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Login extends Model
{
    protected $table = 'login_logs';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
    public function getHistory($user_id, $limit) {
        return $this->select(['browser', 'ip','device', 'created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    

}
