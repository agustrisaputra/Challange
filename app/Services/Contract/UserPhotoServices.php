<?php

namespace App\Services\Contract;

use App\Models\User;

interface UserPhotoServices
{
    public function store(User $user, array $photos): void;
    public function update(User $user, array $photos): void;
}
