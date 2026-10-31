<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



use Illuminate\Support\Facades\DB;

class Charge extends Model
{
    public function payin(float $amount, int $packageId, int $providerId)
    {
        $packageSlab = DB::table('package_slabs')
    ->where('service', 'payin')
    ->where('package_id', $packageId)
    ->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
    ->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
    ->first();

        if (!$packageSlab) {
            return false;
        }

        $chargeValue = $packageSlab->percent ? ($packageSlab->amount * $amount) / 100 : $packageSlab->amount;
        $chargeGST = $packageSlab->gst ? ($chargeValue * 18) / 100 : 0;
        return [
            'charge' => $chargeValue,
            'charge_gst' => $chargeGST
        ];
    }
    
    public function payout(float $amount, int $packageId, int $providerId)
    {
        $packageSlab = DB::table('package_slabs')
            ->where('service', 'payout')
            ->where('package_id', $packageId)
->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
            ->first();

        if (!$packageSlab) {
            return false;
        }

        $chargeValue = $packageSlab->percent ? ($packageSlab->amount * $amount) / 100 : $packageSlab->amount;
        $chargeGST = $packageSlab->gst ? ($chargeValue * 18) / 100 : 0;
        return [
            'charge' => $chargeValue,
            'charge_gst' => $chargeGST
        ];
    }
}
