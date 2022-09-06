<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPhoto;
use App\Services\Contract\UserPhotoServices;

class UserPhotoService implements UserPhotoServices
{
    public function store(User $user, array $photos): void
    {
        $this->save($user, $photos);
        return;
    }

    public function update(User $user, array $photos): void
    {
        $user->photos->delete();
        $this->save($user, $photos);
    }

    protected function save(User $user, array $photos): void
    {
        foreach ($photos as $photo) {
            $userPhoto        = new UserPhoto();
            $userPhoto->image = $photo;

            $user->photos()->save($userPhoto);
        }

        return;
    }
}
