<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserListRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Contract\UserServices;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserServices $userServices)
    {}

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\UserListRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(UserListRequest $request)
    {
        $users = User::with('photos', 'creditCard')
                    ->search($request)
                    ->offset($request->of)
                    ->limit($request->lt)
                    ->orderBy($request->ob, $request->sb)
                    ->get();

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RegisterUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterUserRequest $request)
    {
        $user = $this->userServices->create($request->validated());

        return response()->json(['user_id' => $user->id], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return UserResource::make($user->load('photos', 'creditCard'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request)
    {
        $user = User::findOrFail($request->user_id);

        $this->userServices->update($user, $request->validated());
        return success_response();
    }
}
