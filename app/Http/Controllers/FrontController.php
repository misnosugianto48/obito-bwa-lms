<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FrontController extends Controller
{

    protected $payment;
    protected $transaction;
    protected $pricingService;

    public function __construct(
        PaymentService $paymentService,
        TransactionService $transactionService,
        PricingService $pricingService
    ) {
        $this->payment = $paymentService;
        $this->transaction = $transactionService;
        $this->pricingService = $pricingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('front.index');
    }

    public function pricing()
    {
        $pricingPackages = $this->pricingService->getAllPackages();
        $user = Auth::user();
        return view('front.pricing', compact('pricingPackages', 'user'));
    }

    public function checkout(Pricing $pricing)
    {
        $checkoutData = $this->transaction->prepareCheckout($pricing);

        if ($checkoutData['alreadySubscribed']) {
            return redirect()->route('front.pricing')->with('error', 'You already  subscribed this plan.');
        }

        // return dd($checkoutData);
        return view('front.checkout', $checkoutData);
    }


    public function paymentStoreMidtrans()
    {
        try {
            $pricingId = session()->get('pricing_id');

            if (!$pricingId) {
                return response()->json(['error' => 'No pricing data found in this session'], 404);
            }

            $snapToken = $this->payment->createPayment($pricingId);

            if (!$snapToken) {
                return response()->json(['error' => 'Failed to create Midtrans transaction.'], 500);
            }

            return response()->json(['snapToken' => $snapToken], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function paymentMidtransNotification(Request $request)
    {
        try {
            $transactionStatus = $this->payment->handlePaymentNotification();
            if (!$transactionStatus) {
                return response()->json(['error' => 'Invalid notification data'], 400);
            }

            return response()->json(['status' => $transactionStatus]);
        } catch (\Exception $e) {
            Log::error("Failed to handle midtrans  notification: ", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process notification'], 500);
        }
    }

    public function checkoutSuccess()
    {
        $pricing = $this->transaction->getRecentPricing();

        if ($pricing) {
            return redirect()->route('front.pricing')->with('error', 'No recent subscription found.');
        }

        return view('front.checkout.success', compact('pricing'));
    }
}
