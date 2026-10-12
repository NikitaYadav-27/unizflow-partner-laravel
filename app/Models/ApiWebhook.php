<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class ApiWebhook extends Model
{
    protected $table = 'api_webhooks';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];
    

}
