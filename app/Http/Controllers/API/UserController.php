<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function profile()
    {
        try {
            $user = auth()->user();
            return response()->json(['status' => true, 'data' => $user], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize');
        try {
            $users = User::filter($request)->simplePaginate($pageSize ?? 10);
            return response()->json(['status' => true, 'data' => $users], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json(['status' => true, 'data' => $user], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
