<?php

namespace App\Repositories;

use App\Interfaces\PricingRepositoryInterface;
use App\Models\Pricing;
use Illuminate\Database\Eloquent\Collection;

class PricingRepository implements PricingRepositoryInterface
{
  public function findById(int $id): ?Pricing
  {
    return Pricing::find($id);
  }

  public function findAll(): Collection
  {
    return Pricing::all();
  }
}
