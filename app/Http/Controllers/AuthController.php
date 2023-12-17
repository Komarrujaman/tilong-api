<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ada kesalahan',
                'data' => $validator->errors()
            ]);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] = $user->createToken('auth_token')->plainTextToken;
        $success['name'] = $user->name;
        $success['role_id'] = $user->role_id;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi Sukses',
            'data' => $success
        ]);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();
            $success['token'] = $auth->createToken('auth_token')->plainTextToken;
            $success['name'] = $auth->name;
            $success['role_id'] = $auth->role_id;

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'data' => $success
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cek kembali username & password Anda',
                'data' => null
            ]);
        }
    }

    public function show(Request $request)
    {
        $user = $request->user();

        // Melakukan eager loading dengan model Role
        $userWithRole = $user->load('role');

        return [
            "id" => $userWithRole->id,
            "role_id" => $userWithRole->role_id,
            "role" => $userWithRole->role->role, // Mengambil nama peran (role)
            "name" => $userWithRole->name,
            "email" => $userWithRole->email,
            "email_verified_at" => $userWithRole->email_verified_at,
            "created_at" => $userWithRole->created_at,
            "updated_at" => $userWithRole->updated_at,
        ];
    }
}
