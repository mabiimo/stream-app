<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Package;
use App\Models\UserPremium;
use Illuminate\Support\Carbon;

class WebhookController extends Controller
{
    public function handler(Request $request)
    {
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$serverKey =  env('MIDTRANS_SERVER_KEY');
        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $transactionCode = $notif->order_id;
        $fraudStatus = $notif->fraud_status;

        $status = '';

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $status = 'challenge';
            } else if ($fraudStatus == 'accept') {
                $status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            $status = 'success';
        } else if (
            $transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire'
        ) {
            $status = 'failure';
        } else if ($transactionStatus == 'pending') {
            $status = 'pending';
        }

        $transaction = Transaction::with('package')
            ->where('transaction_code', $transactionCode)
            ->first();

        $customer = auth()->user();

        $package = Package::find($request->package_id);

        if ($status === 'success') {
            $userPremium = UserPremium::where('user_id', $customer->id)->first();

            if ($userPremium) {
                // renewal subscription
                $endOfSubscription = $userPremium->end_of_subscription;
                $date = Carbon::createFromFormat('Y-m-d', $endOfSubscription);
                $newEndOfSubscription = $date->addDays($transaction->$package->id)->format('Y-m-d');

                dd($newEndOfSubscription);

                $userPremium->update([
                    'package_id' => $transaction->$package->id,
                    'end_of_subscription' => $newEndOfSubscription
                ]);
            } else {
                // new subscription
                UserPremium::create([
                    'package_id' => $transaction->$package->id,
                    'user_id' => $customer->id,
                    'end_of_subscription' => now()->addDays($transaction->$package->id)
                ]);
            }
        }

        $transaction->update(['status' => $status]);
    }
}
