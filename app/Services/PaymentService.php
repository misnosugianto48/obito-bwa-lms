<?php

namespace App\Services;

use App\Helpers\TransactionHelper;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Pricing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentService
{

  protected $midtransService;
  protected $pricingRepo;
  protected $transactionRepo;

  public function __construct(
    MidtransService $midtransService,
    PricingRepositoryInterface $pricingRepo,
    TransactionRepositoryInterface $transactionRepo
  ) {
    $this->midtransService = $midtransService;
    $this->pricingRepo = $pricingRepo;
    $this->transactionRepo = $transactionRepo;
  }

  public function createPayment(int $pricingId): string
  {
    $user = Auth::user();

    $pricing = $this->pricingRepo->findById($pricingId);

    $tax = 0.12;
    $totalTax = $pricing->price * $tax;
    $grandTotal = $pricing->price + $totalTax;

    $params = [
      'transaction_details' => [
        'order_id' => TransactionHelper::generateUniqueTrxId(),
        'gross_amount' => (int) $grandTotal,
      ],
      'customer_details' => [
        'first_name' => $user->name,
        'email' => $user->email
      ],
      'item_details' => [
        [
          'id' => $pricing->id,
          'price' => (int) $pricing->price,
          'quantity' => 1,
          'name' => $pricing->name,
        ],
        [
          'id' => 'tax',
          'price' => (int) $totalTax,
          'quantity' => 1,
          'name' => 'Ppn 12%'
        ],
        'custom_field1' => $user->id,
        'custom_field2' => $pricingId
      ]
    ];

    return $this->midtransService->createSnapToken($params);
  }

  public function handlePaymentNotification()
  {
    $notification = $this->midtransService->handleNotification();


    if (in_array($notification['transaction_status'], ['capture', 'settlement'])) {
      $pricing = Pricing::findOrFail($notification['custom_field2']);

      $this->createTransaction($notification, $pricing);
    }
    return $notification['transaction_status'];
  }

  protected function createTransaction(array $notification, Pricing $pricing)
  {
    $startedAt = now();
    $endedAt = $startedAt->copy()->addMonths($pricing->duration);

    $transactionData = [
      'user_id' => $notification['custom_field1'],
      'pricing_id' => $notification['custom_field2'],
      'sub_total_amount' => $pricing->pricing,
      'total_tax_amount' => $pricing->tax * 0.12,
      'grand_total_amount' => $notification['gross_amount'],
      'payment_type' => 'Midtrans',
      'is_paid' => true,
      'booking_trx_id' => $notification['order_id'],
      'started_at' => $startedAt,
      'ended_at' => $endedAt
    ];

    $this->transactionRepo->create($transactionData);

    Log::info("Transaction successfully created: " . $notification['order_id']);
  }
}
