<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\Request;
use App\Models\Role;

class AdminUserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    // Danh sách user
    public function index(Request $request)
    {
        $filters = [
            'q' => $request->input('q'),
            'role_id' => $request->input('role_id'),
        ];
        $perPage = (int) ($request->input('per_page') ?: 15);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;

        $users = $this->userService->paginate($filters, $perPage);
        $roles = Role::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    // Form tạo user
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    // Lưu user mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $this->userService->createUser($validated);

        return redirect()->route('admin.users.index')->with('success', 'Tạo người dùng thành công');
    }

    // Xem chi tiết user
    public function show($id)
    {
        $user = $this->userService->findById($id);
        if (!$user) abort(404);
        return view('admin.users.show', compact('user'));
    }

    // Form sửa user
    public function edit($id)
    {
        $user = $this->userService->findById($id);
        if (!$user) abort(404);
        return view('admin.users.edit', compact('user'));
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $data = $request->only(['name', 'username', 'email', 'phone', 'role_id']);
        $success = $this->userService->updateUser($id, $data);
        
        if (!$success) {
            abort(404);
        }
        
        return redirect()->route('admin.users.show', $id)->with('success', 'Cập nhật thành công!');
    }

    // Xóa user
    public function destroy($id)
    {
        $success = $this->userService->deleteUser($id);
        
        if (!$success) {
            abort(404);
        }
        
        return redirect()->route('admin.users.index')->with('success', 'Đã xóa user!');
    }
} 