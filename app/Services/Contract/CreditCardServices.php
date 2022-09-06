<?php

namespace App\Services\Contract;

use App\Models\User;

interface CreditCardServices
{
    public function store(User $user, array $creditCard): void;
}
