<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Provider extends Model
{
    protected $table = 'providers';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $guarded = [];


}
