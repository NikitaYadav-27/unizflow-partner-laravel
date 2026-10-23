<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementPackageSlot extends Model
{
    protected $table = 'settlement_package_slots';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'package_id',
        'slot_number',
        'settlement_time',
        'txn_from_time',
        'txn_to_time',
        'status',
        'created_at',
        'updated_at'
    ];

    public function package()
    {
        return $this->belongsTo(SettlementPackage::class, 'package_id', 'id');
    }
}
