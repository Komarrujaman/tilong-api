<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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

        if (User::where('email', $input['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar',
                'data' => null
            ]);
        }

        $user = User::create($input);
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
            $success['token'] = $auth->createToken($request->email)->plainTextToken;
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

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada kesalahan',
                'data' => $validator->errors()
            ]);
        }

        $id = $request->input('id');
        $input = $request->only(['name', 'email', 'role_id']);

        // Check if the email is unique, excluding the current user's email
        if (User::where('email', $input['email'])->where('id', '!=', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar',
                'data' => null
            ]);
        }

        // Update the user data
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
                'data' => null
            ]);
        }

        $user->fill($input);

        // Save the user data
        $user->save();

        // You can customize the response as needed
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diupdate',
            'data' => $user
        ]);
    }

    public function allUser()
    {
        $user = User::with('role')->get();
        return $user;
    }
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        // Jika pengguna tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
                'data' => null
            ]);
        }

        // Hapus pengguna
        $user->delete();

        // Berikan respons sukses
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil dihapus',
            'data' => null
        ]);
    }
}
