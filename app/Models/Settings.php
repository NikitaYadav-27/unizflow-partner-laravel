<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Settings extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'value'];
    
    

    public function isPayinEnabled(): bool
    {
        $row = $this->where('name', 'payin_enable')->first();
        return $row && (int)$row['value'] === 1;
    }

    public function isPayoutEnabled(): bool
    {
        $row = $this->where('name', 'payout_enable')->first();
        return $row && (int)$row['value'] === 1;
    }
}
