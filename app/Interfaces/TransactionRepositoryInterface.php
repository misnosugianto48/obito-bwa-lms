<?php

namespace App\Interfaces;

interface TransactionRepositoryInterface
{
  public function findBookingId(string $bookingId);
  public function create(array $data);
  public function getUserTransactions(int $userId);
}
