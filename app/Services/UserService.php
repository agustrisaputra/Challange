<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contract\CreditCardServices;
use App\Services\Contract\UserPhotoServices;
use App\Services\Contract\UserServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class UserService implements UserServices
{
    public function __construct(
        protected UserPhotoServices $userPhotoServices,
        protected CreditCardServices $creditCardServices
    )
    {}

    public function create(array $data): User
    {
        $userData = [...Arr::only($data, ['name', 'email', 'address', 'password'])];

        DB::beginTransaction();
        try {
            $user = new User($userData);
            $user->save();

            $this->userPhotoServices->store($user, $data['photos']);
            $this->creditCardServices->store($user, [
                ...Arr::only($data, [
                    'creditcard_type',
                    'creditcard_number',
                    'creditcard_name',
                    'creditcard_expired',
                    'creditcard_cvv'
                ])
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $user;
    }

    public function update(User $user, array $data): void
    {
        $userData = [...Arr::only($data, ['name', 'email', 'address', 'password'])];

        DB::beginTransaction();
        try {
            $user = $user->fill($userData);
            $user->update();

            $this->userPhotoServices->store($user, $data['photos']);
            $this->creditCardServices->store($user, [
                ...Arr::only($data, [
                    'creditcard_type',
                    'creditcard_number',
                    'creditcard_name',
                    'creditcard_expired',
                    'creditcard_cvv'
                ])
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return;
    }
}
