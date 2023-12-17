<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Roles::all();
        return $role;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => [
                'required',
                'email',
                ValidationRule::unique('roles', 'role')
            ],
        ]);

        $input =  $request->all();

        if (Roles::where('role', $input['role'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Role Sudah Ada',
            ]);
        }

        $role = Roles::create($input);
        $success['role'] = $role->role;

        return response()->json([
            'success' => true,
            'message' => 'Tambah Role Sukses',
            'data' => $success
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
