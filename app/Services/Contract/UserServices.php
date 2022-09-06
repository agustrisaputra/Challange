<?php

namespace App\Services\Contract;

use App\Models\User;

interface UserServices
{
    public function create(array $data): User;
    public function update(User $user, array $data): void;
}
