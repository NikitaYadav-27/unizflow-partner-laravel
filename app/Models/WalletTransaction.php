<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    
  
    

}
