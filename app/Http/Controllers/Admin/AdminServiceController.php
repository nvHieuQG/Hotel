<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\ServiceServiceInterface;
use App\Interfaces\Services\ServiceCategoryServiceInterface;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    protected $serviceService;
    protected $serviceCategoryService;

    public function __construct(ServiceServiceInterface $serviceService, ServiceCategoryServiceInterface $serviceCategoryService)
    {
        $this->serviceService = $serviceService;
        $this->serviceCategoryService = $serviceCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = $this->serviceService->getAll();
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->serviceCategoryService->getAll();
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $this->serviceService->create($validated);
        return redirect()->route('admin.services.index')->with('success', 'Tạo dịch vụ thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $service = $this->serviceService->getById($id);
        $categories = $this->serviceCategoryService->getAll();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $this->serviceService->update($id, $validated);
        return redirect()->route('admin.services.index')->with('success', 'Cập nhật dịch vụ thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->serviceService->delete($id);
        return redirect()->route('admin.services.index')->with('success', 'Xóa dịch vụ thành công!');
    }
}
