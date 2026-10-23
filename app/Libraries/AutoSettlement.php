<?php
namespace App\Libraries;

use App\Models\User;
use App\Models\Settlement;
use App\Models\SettlementPackage;
use App\Models\SettlementPackageSlot;
use App\Models\SettlementRuntime;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AutoSettlement
{
     
    public static function process($targetDate = null, $slotTime = null, $bypassRuntimeCheck = false)
    {
        $today = $targetDate ?: date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($today . ' -1 day'));

        $results = [
            'date' => $today,
            'slot_time' => $slotTime,
            'eligible_auto_users_count' => 0,
            'skipped_manual_users_count' => 0,
            'settlements_created' => 0,
            'transactions_settled' => 0,
            'total_amount_settled' => 0,
            'details' => []
        ];

        if ($slotTime && !$bypassRuntimeCheck) {
            $runtimeKey = "{$today} {$slotTime}:00";
            $alreadyRun = SettlementRuntime::where('time', $runtimeKey)->count() > 0;
            if ($alreadyRun) {
                return array_merge($results, [
                    'status' => 'already_run',
                    'message' => "Settlement already processed for {$runtimeKey}"
                ]);
            }
            SettlementRuntime::create(['time' => $runtimeKey]);
        }

        $unsettledUserRows = Transaction::select('user_id')
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('settled', 0)
            ->groupBy('user_id')
            ->get();

        if ($unsettledUserRows->isEmpty()) {
            return $results;
        }

        $userIds = $unsettledUserRows->pluck('user_id')->toArray();

        $users = User::whereIn('id', $userIds)
            ->where('active', 1)
            ->get()
            ->keyBy('id');

        foreach ($userIds as $userId) {
            $user = $users->get($userId);
            if (!$user) {
                continue;
            }

            $isAuto = strtolower($user->settlement_type ?? 'manual') === 'automatic';
            if (!$isAuto) {
                $results['skipped_manual_users_count']++;
                continue;
            }

            $results['eligible_auto_users_count']++;

            $packageId = $user->settlement_package;
            if (!$packageId) {
                continue;
            }

            $packageAllSlots = SettlementPackageSlot::where('package_id', $packageId)->where('status', 1)->orderBy('slot_number', 'asc')->get();

            if ($packageAllSlots->isEmpty()) {
                // If package has NO slots configured at all, fallback to yesterday full-day
                if ($slotTime && !in_array($slotTime, ['00:00', '00:02', 'all'])) {
                    continue;
                }
                $startDate = "{$yesterday} 00:00:00";
                $endDate = "{$yesterday} 23:59:59";
                $settlement = self::process_settlement($startDate, $endDate, $userId);
                if ($settlement) {
                    $results['settlements_created']++;
                    $results['total_amount_settled'] += floatval($settlement->net_amount);
                    $results['details'][] = [
                        'user_id' => $userId,
                        'merchant_id' => $user->merchant_id,
                        'batch_id' => $settlement->batch_id,
                        'amount' => $settlement->net_amount,
                        'from_time' => $startDate,
                        'to_time' => $endDate
                    ];
                }
                continue;
            }

            // Filter package slots by requested execution time (if provided)
            $slots = $slotTime ? $packageAllSlots->where('settlement_time', $slotTime) : $packageAllSlots;
            if ($slots->isEmpty()) {
                // This package has slots, but none match $slotTime
                continue;
            }

            foreach ($slots as $slot) {
                $fromTime = $slot->txn_from_time;
                $toTime = $slot->txn_to_time;

                if ($fromTime > $toTime) {
                    $startDate = "{$yesterday} {$fromTime}:00";
                    $endDate = "{$today} {$toTime}:59";
                } elseif ($slot->settlement_time <= '04:00' && $toTime >= '20:00') {
                    $startDate = "{$yesterday} {$fromTime}:00";
                    $endDate = "{$yesterday} {$toTime}:59";
                } else {
                    $startDate = "{$today} {$fromTime}:00";
                    $endDate = "{$today} {$toTime}:59";
                }

                $alreadySettled = Settlement::where('user_id', $userId)
                    ->where('from_time', $startDate)
                    ->where('to_time', $endDate)
                    ->exists();

                if ($alreadySettled) {
                    continue;
                }

                $settlement = self::process_settlement($startDate, $endDate, $userId);
                if ($settlement) {
                    $results['settlements_created']++;
                    $results['total_amount_settled'] += floatval($settlement->net_amount);
                    $results['details'][] = [
                        'user_id' => $userId,
                        'merchant_id' => $user->merchant_id,
                        'batch_id' => $settlement->batch_id,
                        'slot_number' => $slot->slot_number,
                        'settlement_time' => $slot->settlement_time,
                        'amount' => $settlement->net_amount,
                        'from_time' => $startDate,
                        'to_time' => $endDate
                    ];
                }
            }
        }

        return $results;
    }

    public static function process_settlement($startDate, $endDate, $userId)
    {
        $transactionModel = new Transaction();
        $settlementModel = new Settlement();

        $result = $transactionModel
            ->selectRaw('sum(amount) as total_amount, sum(charges) as total_charges, sum(gst) as total_gst, count(id) as total_count')
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('settled', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($q1) use ($startDate, $endDate) {
                    $q1->whereNotNull('success_at')
                       ->where('success_at', '>=', $startDate)
                       ->where('success_at', '<=', $endDate);
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNull('success_at')
                       ->whereNotNull('txn_complete_time')
                       ->where('txn_complete_time', '>=', $startDate)
                       ->where('txn_complete_time', '<=', $endDate);
                });
            })
            ->where('user_id', $userId)
            ->first();

        $totalAmount = floatval($result['total_amount'] ?? 0);
        if ($totalAmount > 0) {
            $totalCharges = floatval($result['total_charges'] ?? 0);
            $totalGST = floatval($result['total_gst'] ?? 0);
            $netAmount = $totalAmount - $totalCharges - $totalGST;

            $totalAmountFormatted = number_format($totalAmount, 2, '.', '');
            $totalChargesFormatted = number_format($totalCharges, 2, '.', '');
            $totalGSTFormatted = number_format($totalGST, 2, '.', '');
            $netAmountFormatted = number_format($netAmount, 2, '.', '');

            $batchId = "BTC" . strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20));
            $txnid = "SETL" . strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20));

            DB::beginTransaction();
            try {
                $settlement = $settlementModel->create([
                    "user_id" => $userId,
                    "batch_id" => $batchId,
                    "amount" => $totalAmountFormatted,
                    "charges" => $totalChargesFormatted,
                    "gst" => $totalGSTFormatted,
                    "net_amount" => $netAmountFormatted,
                    "settle_to" => "",
                    "txnid" => $txnid,
                    "status" => "Pending",
                    "from_time" => $startDate,
                    "to_time" => $endDate
                ]);

                $settlementId = $settlement->id;

                $transactionModel
                    ->where('service', 'payin')
                    ->where('status', 'Success')
                    ->where('settled', 0)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->where(function ($q1) use ($startDate, $endDate) {
                            $q1->whereNotNull('success_at')
                               ->where('success_at', '>=', $startDate)
                               ->where('success_at', '<=', $endDate);
                        })->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->whereNull('success_at')
                               ->whereNotNull('txn_complete_time')
                               ->where('txn_complete_time', '>=', $startDate)
                               ->where('txn_complete_time', '<=', $endDate);
                        });
                    })
                    ->where('user_id', $userId)
                    ->update([
                        'settled' => 1,
                        'settlement_id' => $settlementId
                    ]);

                DB::commit();
                return $settlement;
            } catch (\Exception $e) {
                DB::rollBack();
                return null;
            }
        }

        return null;
    }
}
