<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class LoginBrowser extends Model {
    protected $table         = 'login_browsers';
    protected $primaryKey    = 'id';
    protected $guarded = [];
    
    public $timestamps = true;
    
    

    public function findBrowserToken(string $browserToken): ?array
    {
        return $this->where('browser_token', $browserToken)
                    ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                    ->where('created_at', '<=', date('Y-m-d 23:59:59'))
                    ->first();
    }
}
