<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];


    public function checkPhoneExists($phone)
    {
        return $this->where('phone', $phone)->count() > 0;
    }
    
    public function checkEmailExists($email)
    {
        return $this->where('email', $email)->count() > 0;
    }
    
    public function checkMerchantIdExists($merchantId)
    {
        return $this->where('merchant_id', $merchantId)->count() > 0;
    }

    public function getUserByResetToken($token)
    {
        return $this->where('reset_password_token', $token)->first();
    }

    public function updateWallet($userId, $amount)
    {
        return $this->where('id', $userId)->update(['wallet' => $amount]);
    }

    public function hasApiAccess($userId)
    {
        $user = $this->find($userId);
        return $user && $user['api_access'] == 1;
    }
    
    public function findMerchantId($merchantId){
        return $this->where('merchant_id', $merchantId)->first();
    }
}
