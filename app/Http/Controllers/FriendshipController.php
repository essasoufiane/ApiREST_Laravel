<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request, $friendId)
    {
        $user = $request->user();
        $friend = User::findOrFail($friendId);

        if ($user->id === $friend->id) {
            return response()->json(['message' => 'You cannot add yourself as a friend'], 400);
        }

        if ($user->friendsOfMine()->where('friend_id', $friendId)->exists()) {
            return response()->json(['message' => 'Friend request already sent'], 400);
        }

        $user->friendsOfMine()->attach($friendId, ['status' => 'pending']);

        return response()->json(['message' => 'Friend request sent']);
    }

    public function acceptRequest(Request $request, $friendId)
    {
        $user = $request->user();

        $friendship = $user->friendOf()->where('user_id', $friendId)->firstOrFail();
        $friendship->pivot->status = 'accepted';
        $friendship->pivot->save();

        return response()->json([
            'message' => 'Friend request accepted',
            'friend' => $friendship->only(['id', 'name', 'email'])
        ]);
    }

    public function getFriends(Request $request)
    {
        $friends = $request->user()->friends();

        $friends = $friends->map(function ($friend) {
            return [
                'id' => $friend->id,
                'name' => $friend->name,
                'email' => $friend->email,
                // Ajoutez d'autres champs si nécessaire
            ];
        });

        return response()->json($friends);
    }

    public function searchUsers(Request $request)
    {
        Log::info('SearchUsers method called', ['query' => $request->get('query')]);

        $query = $request->get('query');
        $users = User::where('name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->get();
        return response()->json($users);
    }

    public function getPendingFriendRequests(Request $request)
    {
        $user = $request->user();
        $pendingRequests = $user->friendOf()
            ->wherePivot('status', 'pending')
            ->get(['users.id', 'name', 'email']);

        return response()->json($pendingRequests);
    }
}

