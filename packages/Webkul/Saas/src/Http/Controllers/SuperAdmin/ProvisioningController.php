<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Services\ProvisioningService;

class ProvisioningController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProvisioningOperationRepository $operations,
        protected ProvisioningService $service
    ) {}

    /**
     * List provisioning attempts with their step and error.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');

        $operations = $this->operations->scopeQuery(function ($query) use ($status) {
            return $query
                ->with('tenant')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->orderByDesc('created_at');
        })->paginate(20);

        return view('saas::super-admin.provisioning.index', [
            'operations' => $operations,
            'status' => $status,
        ]);
    }

    /**
     * Retry provisioning for the tenant of a failed operation.
     */
    public function retry(int $operation)
    {
        $operation = $this->operations->findOrFail($operation);

        if ($operation->tenant->isServing()) {
            return back()->with('error', trans('saas::app.super-admin.tenants.already-active'));
        }

        $this->service->retry($operation->tenant);

        return back()->with('success', trans('saas::app.super-admin.tenants.provisioning-queued'));
    }
}
