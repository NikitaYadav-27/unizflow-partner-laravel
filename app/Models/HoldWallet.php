<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class HoldWallet extends Model
{
    protected $table = 'hold_wallet_transactions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];

}
