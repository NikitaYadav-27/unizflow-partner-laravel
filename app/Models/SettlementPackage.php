<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementPackage extends Model
{
    protected $table = 'settlement_packages';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name', 'created_at', 'updated_at'
    ];

    public function slots()
    {
        return $this->hasMany(SettlementPackageSlot::class, 'package_id', 'id')->orderBy('slot_number', 'asc');
    }
}
