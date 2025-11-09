<?php

namespace App\Repositories;

use App\Interfaces\PricingRepositoryInterface;
use App\Models\Pricing;
use Illuminate\Support\Collection;

class PricingRepository implements PricingRepositoryInterface
{
  public function findById(int $pricingId): ?Pricing
  {
    return Pricing::find($pricingId);
  }

  public function findAll(): Collection
  {
    return Pricing::all();
  }
}
