<?php

namespace App\Http\Controllers\Users;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Partner;
use App\Models\Stations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function index()
    {
        $data = [];
        return view('/users/index', $data);
    }

    public function getData(Request $request)
    {
        $model = new Account();
        $query = $model->select();
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function create(Request $request)
    {
        $partners = Partner::orderBy('partner_name')->get();
        $accountId = $request->get('account_id');
        return view('users.partials.form', [
            'user' => null, 
            'partners' => $partners,
            'account_id' => $accountId
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'id_role' => 'required|integer|in:1,2',
        ];

        $messages = [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'id_role.required' => 'Role is required',
            'id_role.in' => 'Role must be either Admin (1) or Partner (2)',
        ];

        // Add partner_id validation if role is Partner
        if ($request->id_role == 2) {
            $rules['partner_id'] = 'required|integer|exists:partners,partner_id';
            $messages['partner_id.required'] = 'Partner is required when role is Partner';
            $messages['partner_id.exists'] = 'Selected partner does not exist';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed');
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role,
        ];

        // Add partner_id only if role is Partner
        if ($request->id_role == 2 && $request->filled('partner_id')) {
            $userData['partner_id'] = $request->partner_id;
        } else {
            $userData['partner_id'] = null;
        }

        User::create($userData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        }

        return redirect()->route('cpo.users')
            ->with('success', 'User created successfully');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $partners = Partner::orderBy('partner_name')->get();
        return view('users.partials.form', ['user' => $user, 'partners' => $partners]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'id_role' => 'required|integer|in:1,2',
        ];

        $messages = [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'id_role.required' => 'Role is required',
            'id_role.in' => 'Role must be either Admin (1) or Partner (2)',
        ];

        // Add partner_id validation if role is Partner
        if ($request->id_role == 2) {
            $rules['partner_id'] = 'required|integer|exists:partners,partner_id';
            $messages['partner_id.required'] = 'Partner is required when role is Partner';
            $messages['partner_id.exists'] = 'Selected partner does not exist';
        }

        // Add password validation only if password is provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $messages['password.required'] = 'Password is required';
            $messages['password.min'] = 'Password must be at least 8 characters';
            $messages['password.confirmed'] = 'Password confirmation does not match';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'id_role' => $request->id_role,
        ];

        // Add partner_id only if role is Partner
        if ($request->id_role == 2 && $request->filled('partner_id')) {
            $data['partner_id'] = $request->partner_id;
        } else {
            $data['partner_id'] = null;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        }

        return redirect()->route('cpo.users')
            ->with('success', 'User updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id == auth()->id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }
            return redirect()->route('cpo.users')
                ->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }

        return redirect()->route('cpo.users')
            ->with('success', 'User deleted successfully');
    }

    public function detail($id)
    {
        $account = Account::findOrFail($id);
        
        // Get stations where stations.account_id = accounts.account_id
        $stations = Stations::where('account_id', $account->account_id)->get();
        
        // Get users where users.partner_id = accounts.account_id
        $users = User::where('partner_id', $account->account_id)->get();
        
        return view('users.detail', [
            'account' => $account,
            'stations' => $stations,
            'users' => $users
        ]);
    }
}

