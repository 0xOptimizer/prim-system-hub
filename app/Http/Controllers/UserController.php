<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationSuccess;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function show($uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'user_type' => 'required|string',
            'name' => 'sometimes|string|unique:users,name|max:255',
        ]);

        if (empty($request->name)) {
            $firstName = trim($validated['first_name']);
            $lastName = trim($validated['last_name']);

            $firstNamePart = mb_substr($firstName, 0, 8, 'UTF-8');
            $firstNamePart = str_replace('.', '_', $firstNamePart);
            
            $lastNamePart = mb_substr($lastName, 0, 8, 'UTF-8');
            $lastNamePart = str_replace('.', '_', $lastNamePart);

            $baseUsername = strtolower($firstNamePart . '.' . $lastNamePart);
            $username = null;

            for ($i = 0; $i < 10; $i++) {
                $randomNumber = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $candidate = $baseUsername . '.' . $randomNumber;

                if (!User::where('name', $candidate)->exists()) {
                    $username = $candidate;
                    break;
                }
            }

            if (!$username) {
                return response()->json([
                    'error' => 'Could not generate a unique username after 10 attempts. Please provide a username.'
                ], 422);
            }
        } else {
            $username = $validated['name'];
        }

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $username,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'persistent_token' => Str::random(32),
            'user_image' => $request->user_image,
        ]);

        Mail::to($user->email)->send(new RegistrationSuccess($user));

        return response()->json($user, 201);
    }

    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
            'user_type' => 'sometimes|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy($uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}