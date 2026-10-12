<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Key extends Model
{
    protected $table = 'enc_keys';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
    public function findUser($user_id){
        return $this->where("user_id", $user_id)->first();
    }

}
