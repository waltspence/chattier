<?php

namespace App\Http\Controllers;

use App\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $friends = $user->friends();

        try {
            JWTAuth::parseToken()->authenticate();
            $friendship = auth()->user()->checkFriendship($user);
        } catch (JWTException $e) {
            $friendship = 'unauthenticated';
        }

        return response()->json(compact('user', 'friends', 'friendship'));
    }

    public function update()
    {
        $data = request()->validate([
            'firstname' => 'required|max:40',
            'lastname'  => 'required|max:40',
            'location'  => 'max:20',
        ]);

        auth()->user()->update($data);

        return response()->json(['flash' => 'Your profile has been updated.']);
    }

    public function statuses(User $user)
    {
        $cursor = (request()->has('cursor') ? ['id' => request('cursor')] : []);

        $statuses = $user->statuses()->topLevel()
            ->cursorPaginate(5, $cursor, 'desc');

        return response()->json($statuses);
    }
}
