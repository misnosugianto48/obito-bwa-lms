<?php

namespace App\Interfaces;

use App\Models\Pricing;
use Illuminate\Support\Collection;

interface PricingRepositoryInterface
{
  public function findById(int $id): ?Pricing;

  public function findAll(): Collection;
}
