<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCreditCard;
use App\Services\Contract\CreditCardServices;

class CreditCardService implements CreditCardServices
{
    public function store(User $user, array $creditCard): void
    {
        $user->creditCard()->firstOrNew([
            'user_id' => $user->id
        ],
        [
            'type'    => $creditCard['creditcard_type'],
            'number'  => $creditCard['creditcard_number'],
            'name'    => $creditCard['creditcard_name'],
            'expired' => $creditCard['creditcard_expired'],
            'cvv'     => $creditCard['creditcard_cvv']
        ]);

        return;
    }
}
