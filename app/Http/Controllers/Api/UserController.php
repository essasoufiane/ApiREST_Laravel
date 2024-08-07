<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'firstname' => 'nullable|string|max:255',
        'age' => 'nullable|integer|min:0|max:120',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name' => $validatedData['name'],
        'firstname' => $validatedData['firstname'],
        'age' => $validatedData['age'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
    ]);

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


/**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    Log::info('Update method called', ['request' => $request->all(), 'id' => $id]);

    try {
        // Récupérer l'utilisateur connecté
        $currentUser = $request->user();

        // Vérifier si l'utilisateur connecté est celui qu'on essaie de modifier
        if ($currentUser->id != $id) {
            Log::warning('Unauthorized update attempt', ['user_id' => $currentUser->id, 'target_id' => $id]);
            return response()->json(['error' => 'You are not authorized to update this user.'], 403);
        }

        $user = User::findOrFail($id);

        Log::info('User found', ['user' => $user]);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'firstname' => 'sometimes|nullable|string|max:255',
            'age' => 'sometimes|nullable|integer|min:0|max:120',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        Log::info('Validation passed');

        // Traiter le mot de passe séparément s'il est fourni
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Mettre à jour l'utilisateur
        $user->update($validatedData);

        Log::info('User updated', ['user' => $user]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating user', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Log::info('Destroy method called', ['id' => $id]);

        try {
            $user = User::findOrFail($id);
            Log::info('User found', ['user' => $user]);

            $user->delete();
            Log::info('User deleted successfully');

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Révoquer le token actuel...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
