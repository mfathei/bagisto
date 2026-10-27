<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\AuditLogRepository;

class AuditLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AuditLogRepository $audit) {}

    /**
     * List audit entries with filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['tenant_id', 'action', 'search']);

        return view('saas::super-admin.audit.index', [
            'logs' => $this->audit->paginateFiltered($filters),
            'filters' => $filters,
        ]);
    }
}
