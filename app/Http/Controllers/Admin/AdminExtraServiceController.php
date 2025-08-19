<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\ExtraServiceServiceInterface;

class AdminExtraServiceController extends Controller
{
    protected $extraServiceService;

    public function __construct(ExtraServiceServiceInterface $extraServiceService)
    {
        $this->extraServiceService = $extraServiceService;
    }
    public function index(){
        $extraServices = $this->extraServiceService->getAllExtraServices();
        return view('admin.extra-services.index', compact('extraServices'));
    }

    public function create()
    {
        return view('admin.extra-services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'applies_to' => 'required|in:adult,child,both',
            'price_adult' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'charge_type' => 'required|in:per_person,per_night,per_service,per_hour,per_use',
            'is_active' => 'required|boolean',
            'child_age_min' => 'nullable|integer|min:0',
            'child_age_max' => 'nullable|integer|min:0',
        ]);
        $result = $this->extraServiceService->createExtraService($validated);
        if ($result) {
            return redirect()->route('admin.extra-services.index')->with('success', 'Thêm dịch vụ bổ sung thành công!');
        }
        return back()->withInput()->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
    }

    public function edit($id)
    {
        $extraService = $this->extraServiceService->findOrFail($id);
        if (!$extraService) {
            return redirect()->route('admin.extra-services.index')->with('error', 'Dịch vụ bổ sung không tồn tại!');
        }
        return view('admin.extra-services.edit', compact('extraService'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'applies_to' => 'required|in:adult,child,both',
            'price_adult' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'charge_type' => 'required|in:per_person,per_night,per_service,per_hour,per_use',
            'is_active' => 'required|boolean',
            'child_age_min' => 'nullable|integer|min:0',
            'child_age_max' => 'nullable|integer|min:0',
        ]);
        $result = $this->extraServiceService->updateExtraService($id, $validated);
        if ($result) {
            return redirect()->route('admin.extra-services.index')->with('success', 'Cập nhật dịch vụ bổ sung thành công!');
        }
        return back()->withInput()->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
    }

    public function destroy($id)
    {
        $result = $this->extraServiceService->destroyExtraService($id);
        if ($result) {
            return redirect()->route('admin.extra-services.index')->with('success', 'Xóa dịch vụ bổ sung thành công!');
        }
        return redirect()->route('admin.extra-services.index')->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
    }
}
