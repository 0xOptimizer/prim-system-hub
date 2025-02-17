<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an incoming login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required_without:name|email',
            'name' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        $user = null;

        if (isset($credentials['email'])) {
            $user = User::where('email', $credentials['email'])->first();
        } elseif (isset($credentials['name'])) {
            $user = User::where('name', $credentials['name'])->first();
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $user->createToken('prim')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user
        ], 200);
    }
    
    public function validateToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!str_contains($value, '|')) {
                        $fail('Invalid token format.');
                        return;
                    }

                    [$id, $plainTextToken] = explode('|', $value, 2);
                    $hashedToken = hash('sha256', $plainTextToken);

                    $tokenRecord = DB::table('personal_access_tokens')
                        ->where('id', $id)
                        ->where('token', $hashedToken)
                        ->first();

                    if (!$tokenRecord) {
                        $fail('Invalid token.');
                        return;
                    }

                    if ($tokenRecord->expires_at && Carbon::parse($tokenRecord->expires_at)->isPast()) {
                        $fail('Token has expired.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $responseData = [
            'status' => 'success',
            'message' => 'Token is valid.',
        ];

        if (in_array('user', $request->input('with', []))) {
            [$id, $plainTextToken] = explode('|', $request->token, 2);
            $hashedToken = hash('sha256', $plainTextToken);

            $tokenRecord = DB::table('personal_access_tokens')
                ->where('id', $id)
                ->where('token', $hashedToken)
                ->first();

            if ($tokenRecord) {
                $user = User::find($tokenRecord->tokenable_id);

                if ($user) {
                    $responseData['user'] = [
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'id' => $user->id,
                        'uuid' => $user->uuid,
                    ];
                }
            }
        }

        return response()->json($responseData);
    }
    // public function easterEgg(Request $request)
    // {
    //     return true;
    // }
}
