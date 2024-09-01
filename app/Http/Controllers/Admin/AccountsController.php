<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountsController extends Controller
{

    public function show()
    {
        $superAdmin = User::find(1);
        $admins = User::where('role', 'admin')->where('id', '!=', $superAdmin->id)->get();
        return view('admin.account.index', compact('superAdmin', 'admins'));
    }

    public function updateMain(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(User::find(1)->id)],
            'phone_number' => 'required|string|max:255'
        ]);
        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            $user = User::find(1);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->save();
            toast('تم تحديث الحساب بنجاح');
            return back();
        } else {
            Alert::error('لا يمكن تحديث معلومات الحساب الرئيسي الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function updateMainPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            if (!Hash::check($request->old_password, $authUser->password)) {
                Alert::error('كلمة المرور القديمة غير صحيحة');
                return back();
            }

            $authUser->password = Hash::make($request->password);
            $authUser->save();
            toast('تم تغيير كلمة المرور بنجاح');

            // log out the user
            Auth::guard('web')->logout();
            return redirect()->route('admin.login');
        } else {
            Alert::error('لا يمكن تغيير كلمة المرور الرئيسية الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function createAdmin(Request $request)
    {

        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن إضافة مشرف جديد الا بواسطة المشرف الرئيسي');
            return back();
        }

        $request->validate([
            'new_admin_name' => 'required|string|max:255',
            'new_admin_email' => 'required|string|email|max:255|unique:users,email',
            'new_admin_phone_number' => 'required|string|max:255',
            'new_admin_password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->new_admin_name,
            'email' => $request->new_admin_email,
            'phone_number' => $request->new_admin_phone_number,
            'password' => Hash::make($request->new_admin_password),
            'role' => 'admin',
        ]);
        toast('تم إضافة المشرف بنجاح');
        return back();
    }

    public function updateAdmin(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن تحديث معلومات الحساب  الا بواسطة المشرف الرئيسي');
            return back();
        }
        $request->validate([
            'admin_id' => 'required|integer',
            'admin_name' => 'string|max:255',
            'admin_phone_number' => 'required|string|max:255',
            'admin_email' => ['string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->admin_id)]
        ]);
        // dd($request->all());

        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            $admin = User::findOrFail($request->admin_id);
            $admin->name = $request->admin_name;
            $admin->email = $request->admin_email;
            $admin->phone_number = $request->admin_phone_number;
            $admin->save();
            toast('تم تحديث الحساب بنجاح');
            return back();
        } else {
            Alert::error('لا يمكن تحديث معلومات الحساب  الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function deleteAdmin($admin_id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن حذف مشرف الا بواسطة المشرف الرئيسي');
            return back();
        }
        $admin = User::findOrFail($admin_id);

        if ($admin->role !== 'admin') {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا الحساب']);
        }

        $admin->forceDelete();
        toast('تم حذف المشرف بنجاح');
        return back();
    }
}
