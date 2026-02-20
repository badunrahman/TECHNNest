<?php

declare(strict_types=1);

namespace App\Domain\Services;

class OrderValidationService extends BaseService
{

    // simple function to make sure checkout info is legit before we process it
    public function validateCheckout(array $data, float $totalAmount): array
    {
        $errors = [];

        if (empty($data['paymentMethod'])) {
             $errors[] = 'Payment method is required.';
        }

        if ($totalAmount <= 0) {
            $errors[] = 'Order total cannot be zero.';
        }



        return $errors;
    }
}
