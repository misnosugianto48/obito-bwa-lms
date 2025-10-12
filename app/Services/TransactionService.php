<?php

namespace App\Services;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Pricing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
  protected $transactionRepo;

  public function __construct(
    TransactionRepositoryInterface $transactionRepo
  ) {
    $this->transactionRepo = $transactionRepo;
  }

  public function prepareCheckout(Pricing $pricing)
  {
    $user = Auth::user();
    $alreadySubscribed = $pricing->isSubscribedByUser($user->id);

    $tax = 0.12;
    $total_tax_amount = $pricing->price * $tax;
    $sub_total_amount = $pricing->price;
    $grand_total_amount = $sub_total_amount + $total_tax_amount;

    $started_at = now();
    $ended_at = $started_at->copy()->addMonth($pricing->duration);

    session()->put('pricing_id', $pricing->id);

    return compact(
      'alreadySubscribed',
      'tax',
      'total_tax_amount',
      'sub_total_amount',
      'grand_total_amount',
      'started_at',
      'ended_at',
      'user'
    );
  }

  public function getRecentPricing()
  {
    $pricingId = session()->get('pricing_id');
    return Pricing::find($pricingId);
  }

  public function getUserTransactions(): Collection
  {
    $user = Auth::user();

    return $this->transactionRepo->getUserTransactions($user->id);
  }
}
