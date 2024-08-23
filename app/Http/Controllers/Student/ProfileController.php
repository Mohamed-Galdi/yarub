<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('student.account.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            // if ($user->avatar) {
            //     Storage::disk('public')->delete('users_avatars/' . $user->avatar);
            // }

            // Store new avatar
            $avatarName = 'user_id_' . $user->id . time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('public/users_avatars', $avatarName);
            $user->avatar = 'storage/users_avatars/' . $avatarName;
        }

        $user->save();

        return response()->json(['message' => 'تم تحديث الحساب بنجاح']);
    }

    public function updatePassword(Request $request)
    {
        // $request->validate([
        //     'current_password' => 'required',
        //     'new_password' => 'required|string|min:8',
        // ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Auth::logout();

        return response()->json(['message' => 'Password updated successfully. Please log in with your new password.']);
    }
}
