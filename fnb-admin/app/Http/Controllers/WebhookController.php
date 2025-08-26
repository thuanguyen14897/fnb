<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function webhookAlepay()
    {
        $data = $this->request->input();
        if (!empty($data)) {
            if (!empty($data['transactionInfo'])) {
                $transactionInfo = $data['transactionInfo'];
                $orderCode = !empty($transactionInfo['orderCode']) ? $transactionInfo['orderCode'] : null;
                $orderCodeCheck = explode('-', $orderCode);
                $type_transaction = 0;
                if ($orderCodeCheck[0] == 'GD') {
                    $transaction = Transaction::where('reference_no', $orderCode)->first();
                    $type_transaction = 1;
                } else {
                    $transaction = TransactionDriver::where('reference_no', $orderCode)->first();
                    $type_transaction = 2;
                }
                DB::table('tbl_transaction_alepay')->insert([
                    'transactionCode' => !empty($transactionInfo['transactionCode']) ? $transactionInfo['transactionCode'] : null,
                    'transaction_kanow_id' => !empty($transaction) ? $transaction->id : 0,
                    'orderCode' => !empty($transactionInfo['orderCode']) ? $transactionInfo['orderCode'] : null,
                    'amount' => !empty($transactionInfo['amount']) ? $transactionInfo['amount'] : 0,
                    'currency' => !empty($transactionInfo['currency']) ? $transactionInfo['currency'] : null,
                    'buyerEmail' => !empty($transactionInfo['buyerEmail']) ? $transactionInfo['buyerEmail'] : null,
                    'buyerPhone' => !empty($transactionInfo['buyerPhone']) ? $transactionInfo['buyerPhone'] : null,
                    'cardNumber' => !empty($transactionInfo['cardNumber']) ? $transactionInfo['cardNumber'] : null,
                    'buyerName' => !empty($transactionInfo['buyerName']) ? $transactionInfo['buyerName'] : null,
                    'cardHolderName' => !empty($transactionInfo['cardHolderName']) ? $transactionInfo['cardHolderName'] : null,
                    'status' => !empty($transactionInfo['status']) ? $transactionInfo['status'] : null,
                    'message' => !empty($transactionInfo['message']) ? $transactionInfo['message'] : null,
                    'installment' => !empty($transactionInfo['installment']) ? $transactionInfo['installment'] : null,
                    'is3D' => !empty($transactionInfo['is3D']) ? $transactionInfo['is3D'] : null,
                    'month' => !empty($transactionInfo['month']) ? $transactionInfo['month'] : 0,
                    'bankCode' => !empty($transactionInfo['bankCode']) ? $transactionInfo['bankCode'] : null,
                    'bankName' => !empty($transactionInfo['bankName']) ? $transactionInfo['bankName'] : null,
                    'bankHotline' => !empty($transactionInfo['bankHotline']) ? $transactionInfo['bankHotline'] : null,
                    'method' => !empty($transactionInfo['method']) ? $transactionInfo['method'] : null,
                    'bankType' => !empty($transactionInfo['bankType']) ? $transactionInfo['bankType'] : null,
                    'successTime' => !empty($transactionInfo['successTime']) ? $transactionInfo['successTime'] : null,
                    'merchantFee' => !empty($transactionInfo['merchantFee']) ? $transactionInfo['merchantFee'] : null,
                    'payerFee' => !empty($transactionInfo['payerFee']) ? $transactionInfo['payerFee'] : null,
                    'reason' => !empty($transactionInfo['reason']) ? $transactionInfo['reason'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'type_transaction' => $type_transaction
                ]);
            }

            if (!empty($data['refundInfo'])) {
                $refundInfo = $data['refundInfo'];
                $orderCode = !empty($refundInfo['orderCode']) ? $refundInfo['orderCode'] : null;
                $transaction = TransactionDriver::where('reference_no', $orderCode)->first();
                DB::table('tbl_refund_alepay')->insert([
                    'transaction_kanow_id' => !empty($transaction) ? $transaction->id : 0,
                    'refundCode' => !empty($refundInfo['refundCode']) ? $refundInfo['refundCode'] : null,
                    'transactionCode' => !empty($refundInfo['transactionCode']) ? $refundInfo['transactionCode'] : null,
                    'orderCode' => !empty($refundInfo['orderCode']) ? $refundInfo['orderCode'] : null,
                    'refundAmount' => !empty($refundInfo['refundAmount']) ? $refundInfo['refundAmount'] : 0,
                    'totalRefundToPayer' => !empty($refundInfo['totalRefundToPayer']) ? $refundInfo['totalRefundToPayer'] : 0,
                    'refundFee' => !empty($refundInfo['refundFee']) ? $refundInfo['refundFee'] : 0,
                    'reason' => !empty($refundInfo['reason']) ? $refundInfo['reason'] : null,
                    'refundStatus' => !empty($refundInfo['refundStatus']) ? $refundInfo['refundStatus'] : null,
                    'refundTime' => !empty($refundInfo['refundTime']) ? $refundInfo['refundTime'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if (!empty($data['cardTokenInfo'])) {
                $cardTokenInfo = $data['cardTokenInfo'];
//                DB::table('tbl_customer_card_alepay')->insert([
//                    'cardLinkStatus' => !empty($cardTokenInfo['cardLinkStatus']) ? $cardTokenInfo['cardLinkStatus'] : null,
//                    'email' => !empty($cardTokenInfo['email']) ? $cardTokenInfo['email'] : null,
//                    'customerId' => !empty($cardTokenInfo['customerId']) ? $cardTokenInfo['customerId'] : null,
//                    'token' => !empty($cardTokenInfo['token']) ? $cardTokenInfo['token'] : null,
//                    'cardNumber' => !empty($cardTokenInfo['cardNumber']) ? $cardTokenInfo['cardNumber'] : null,
//                    'cardHolderName' => !empty($cardTokenInfo['cardHolderName']) ? $cardTokenInfo['cardHolderName'] : null,
//                    'cardExpireMonth' => !empty($cardTokenInfo['cardExpireMonth']) ? $cardTokenInfo['cardExpireMonth'] : null,
//                    'cardExpireYear' => !empty($cardTokenInfo['cardExpireYear']) ? $cardTokenInfo['cardExpireYear'] : null,
//                    'paymentMethod' => !empty($cardTokenInfo['paymentMethod']) ? $cardTokenInfo['paymentMethod'] : null,
//                    'bankCode' => !empty($cardTokenInfo['bankCode']) ? $cardTokenInfo['bankCode'] : null,
//                    'reason' => !empty($cardTokenInfo['reason']) ? $cardTokenInfo['reason'] : null,
//                    'status' => !empty($cardTokenInfo['status']) ? $cardTokenInfo['status'] : null,
//                    'bankType' => !empty($cardTokenInfo['bankType']) ? $cardTokenInfo['bankType'] : null,
//                    'created_at' => date('Y-m-d H:i:s'),
//                ]);
            }
        }
    }

    public function webhookStripe(){
        $data = $this->request->input();
        \Log::info('stripe',$data);
    }
}
