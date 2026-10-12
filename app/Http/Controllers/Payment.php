<?php
namespace App\Http\Controllers;

use Exception;

class Payment extends Controller {
    
    public function link($txnid) {
        // Decrypt transaction ID
        $encTxnId = $txnid;
        try {
            $decryptedTxnid = openssl_decrypt(
                hex2bin($txnid), 
                'AES-256-CBC', 
                env("encryption.key"), 
                OPENSSL_RAW_DATA, 
                substr(hash('sha256', env("encryption.key")), 0, 16)
            );
            
            // Check if decryption was successful
            if ($decryptedTxnid === false) {
                throw new Exception("Decryption failed");
            }
            
            // Assign decrypted value to txnid
            $txnid = $decryptedTxnid;
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Link not found',
            ], 401);
        }
        
        // Load transaction from database
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByTxnid($txnid);
        
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Link not found',
            ], 401);
        }
        
        return view('payment',["txn" => $transaction, 'encTxnId' => $encTxnId]);
        
        
    }
    
    public function status() {
        $txnid = request()->post("txnid");
        try {
            $decryptedTxnid = openssl_decrypt(
                hex2bin($txnid), 
                'AES-256-CBC', 
                env("encryption.key"),
                OPENSSL_RAW_DATA, 
                substr(hash('sha256', env("encryption.key")), 0, 16)
            );
            
            // Check if decryption was successful
            if ($decryptedTxnid === false) {
                throw new Exception("Decryption failed");
            }
            
            // Assign decrypted value to txnid
            $txnid = $decryptedTxnid;
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Link not found',
            ], 401);
        }
        
        // Load transaction from database
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByTxnid($txnid);
        
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Link not found',
            ], 401);
        }
        
            return response()->json([
                'status' => $transaction['status']
            ], 200);
        
        
    }
}