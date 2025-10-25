<?php

return [
  'serverKey' => env('MIDTRANS_SERVER_KEY'),
  'clientKey' => env('MIDTRANS_CLIENT_KEY'),
  'isProduction' => env('MIDTRANS_PRODUCTION'),
  'isSanitized' => env('MIDTRANS_SANITIZED'),
  'isS3ds' => env('MDITRANS_3DS'),
];
