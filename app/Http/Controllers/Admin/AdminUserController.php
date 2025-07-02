<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // Danh sách user
    public function index()
    {
        $users = $this->userRepository->getAll();
        return view('admin.users.index', compact('users'));
    }

    // Xem chi tiết user
    public function show($id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) abort(404);
        return view('admin.users.show', compact('user'));
    }

    // Form sửa user
    public function edit($id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) abort(404);
        return view('admin.users.edit', compact('user'));
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) abort(404);
        $data = $request->only(['name', 'username', 'email', 'phone', 'role_id']);
        $this->userRepository->update($user, $data);
        return redirect()->route('admin.users.show', $user->id)->with('success', 'Cập nhật thành công!');
    }

    // Xóa user
    public function destroy($id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) abort(404);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Đã xóa user!');
    }
} 