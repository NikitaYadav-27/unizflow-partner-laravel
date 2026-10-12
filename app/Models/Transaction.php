<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = true;
    
    

    public function isTxnIdExists($txnid)
    {
        return $this->where('txnid', $txnid)->count() > 0;
    }
    
    public function isReferenceIdExists($referenceId)
    {
        return $this->where('reference_id', $referenceId)->count() > 0;
    }
    
    public function isMerchantTxnIdExists($merchantTxnId)
    {
        return $this->where('merchant_txnid', $merchantTxnId)->count() > 0;
    }
    
    public function isOrderIdExists($orderId)
    {
        return $this->where('order_id', $orderId)->count() > 0;
    }
    
    public function isRrnExists($rrn)
    {
        return $this->where('rrn', $rrn)->count() > 0;
    }
    public function findTxnByTxnid($txnid){
        return $this->where('txnid', $txnid)->first();
    }
    
    public function findTxnByMerchantId($txnid){
        return $this->where('merchant_txnid', $txnid)->first();
    }
    
    public function getTodayData($user_id)
    {
        $startTime = date("Y-m-d 00:00:00");
        $endTime = date("Y-m-d 23:59:59");
    
        $builder = $this->newQuery();
    
        $builder->selectRaw(
            'SUM(CASE WHEN service = "payin" THEN amount ELSE 0 END) AS collection, ' .
            'SUM(CASE WHEN service = "payout" THEN amount ELSE 0 END) AS payout, ' .
            'SUM(charges + gst) AS total_charges'
        )
        ->where('user_id', $user_id)
        ->where('status', 'Success')
        ->where('txn_complete_time', '>=', $startTime)
        ->where('txn_complete_time', '<=', $endTime);
    
        $result = $builder->first();
        if ($result) { $result = $result->toArray(); }
    
        return [
            'collection' => number_format(($result['collection'] ?? 0), 2, '.', ''),
            'payout'     => number_format(($result['payout'] ?? 0), 2, '.', ''),
            'charges'    => number_format(($result['total_charges'] ?? 0), 2, '.', ''),
        ];
        
    }
    
    public function getTotalData($user_id){
        $builder = $this->newQuery();
    
        $builder->selectRaw(
            'SUM(CASE WHEN service = "payin" THEN amount ELSE 0 END) AS collection, ' .
            'SUM(CASE WHEN service = "payout" THEN amount ELSE 0 END) AS payout, ' .
            'SUM(charges + gst) AS total_charges'
        )
        ->where('user_id', $user_id)
        ->where('status', 'Success');
        $result = $builder->first();
        if ($result) { $result = $result->toArray(); }
    
        return [
            'collection' => number_format(($result['collection'] ?? 0), 2, '.', ''),
            'payout'     => number_format(($result['payout'] ?? 0), 2, '.', ''),
            'charges'    => number_format(($result['total_charges'] ?? 0), 2, '.', ''),
        ];
      
    }
    
    public function getStatusData($user_id)
    {
        $builder = $this->newQuery();
    
        $builder->selectRaw(
            'COUNT(*) AS total_transactions, ' .
            'SUM(CASE WHEN status = "Success" THEN 1 ELSE 0 END) AS success_transactions, ' .
            'SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) AS pending_transactions, ' .
            'SUM(CASE WHEN status = "Failed" THEN 1 ELSE 0 END) AS failed_transactions, ' .
            'SUM(CASE WHEN status = "Processing" THEN 1 ELSE 0 END) AS processing_transactions, ' .
            'SUM(CASE WHEN status = "Expired" THEN 1 ELSE 0 END) AS expired_transactions'
        )
        ->where('user_id', $user_id);
    
        $result = $builder->first();
        if ($result) { $result = $result->toArray(); }
    
        return [
            'total_transactions'    => (int) ($result['total_transactions'] ?? 0),
            'success_transactions'  => (int) ($result['success_transactions'] ?? 0),
            'pending_transactions'  => (int) ($result['pending_transactions'] ?? 0),
            'failed_transactions'   => (int) ($result['failed_transactions'] ?? 0),
            'processing_transactions' => (int) ($result['processing_transactions'] ?? 0),
            'expired_transactions'  => (int) ($result['expired_transactions'] ?? 0),
        ];
    }
    
    public function getRecentTransactions($user_id, $limit)
    {
        return $this->select(['service', 'txnid', 'status', 'amount', 'created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }
    
    public function getWeeklySummary($user_id)
    {
        // Get raw data from DB
        $data = $this->newQuery()
            ->selectRaw(
                'DATE(created_at) as date, ' .
                'SUM(CASE WHEN service = "payin" THEN amount ELSE 0 END) AS payin, ' .
                'SUM(CASE WHEN service = "payout" THEN amount ELSE 0 END) AS payout'
            )
            ->where('user_id', $user_id)
            ->where('status', 'Success')
            ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->groupBy(\DB::raw('DATE(created_at)'))
            ->get()
            ->toArray();
    
        // Map results by date for fast lookup
        $map = array_column($data, null, 'date');
    
        // Build final 7-day summary
        $summary = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $summary[] = [
                'txn_date'     => $date,
                'total_payin'  => isset($map[$date]) ? (float)$map[$date]['payin'] : 0.0,
                'total_payout' => isset($map[$date]) ? (float)$map[$date]['payout'] : 0.0,
            ];
        }
    
        return $summary;
    }
    
    public function getPendingDebitPayouts($limit){
        return $this->where('service","payout")->where("status","Pending")->where("debited",0)->limit($limit)->get();
    }
    
    public function getFailedNonRefundedPayouts() {
        return $this->where("service","payout")->where("status","Failed")->where("debited",1)->where("refunded",0)->get();
    }
    
    public function getTodaysPayinUser() {
        $startTime = date("Y-m-d 00:00:00");
        $endTime = date("Y-m-d 23:59:59");
        return $this->selectRaw("user_id, sum(amount) as amount")
            ->where("service","payin")
            ->where("status","Success")
            ->where("txn_complete_time', '>=', $startTime)
            ->where('txn_complete_time', '<=', $endTime)
            ->groupBy("user_id")
            ->get();
    }
    
    public function getTodaysPayoutUser() {
        $startTime = date("Y-m-d 00:00:00");
        $endTime = date("Y-m-d 23:59:59");
        return $this->selectRaw("user_id, sum(amount) as amount")
            ->where('service","payout")
            ->where("status","Success")
            ->where("txn_complete_time', '>=', $startTime)
            ->where('txn_complete_time', '<=', $endTime)
            ->groupBy("user_id")
            ->get();
    }
    
    public function getTodaysProviderUsage() {
       $startTime = date("Y-m-d 00:00:00");
       $endTime   = date("Y-m-d 23:59:59");
       return $this->selectRaw("payin_provider as provider_id, sum(amount) as total_amount")
           ->where('service","payin")
           ->where("status", "Success")
           ->where("txn_complete_time', '>=', $startTime)
           ->where('txn_complete_time', '<=', $endTime)
           ->groupBy("payin_provider")
           ->get();
    }


}