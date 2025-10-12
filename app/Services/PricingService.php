<?php

namespace App\Services;

use App\Interfaces\PricingRepositoryInterface;
use App\Models\Pricing;

class PricingService
{
  protected $pricingRepo;

  public function __construct(
    PricingRepositoryInterface $pricingRepo
  ) {
    $this->pricingRepo = $pricingRepo;
  }

  public function getAllPackages()
  {
    return $this->pricingRepo->findAll();
  }
}
