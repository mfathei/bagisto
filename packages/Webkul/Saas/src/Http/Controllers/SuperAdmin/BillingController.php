<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Mail\PaymentRecordedMail;
use Webkul\Saas\Repositories\BillingRecordRepository;
use Webkul\Saas\Services\BillingService;

class BillingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BillingRecordRepository $invoices,
        protected BillingService $billing
    ) {}

    /**
     * List invoices with filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'tenant_id']);

        $invoices = $this->invoices->scopeQuery(function ($query) use ($filters) {
            return $query
                ->with('tenant')
                ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
                ->when(! empty($filters['tenant_id']), fn ($query) => $query->where('tenant_id', $filters['tenant_id']))
                ->orderByDesc('created_at');
        })->paginate(20);

        return view('saas::super-admin.billing.index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'provider' => $this->billing->provider(),
        ]);
    }

    /**
     * Show an invoice with its payment transactions.
     */
    public function show(int $invoice)
    {
        $invoice = $this->invoices->findOrFail($invoice);

        $invoice->load(['tenant', 'subscription.plan', 'transactions']);

        return view('saas::super-admin.billing.show', [
            'invoice' => $invoice,
            'provider' => $this->billing->provider(),
        ]);
    }

    /**
     * Record an offline payment attested by the operator.
     */
    public function recordPayment(Request $request, int $invoice)
    {
        $invoice = $this->invoices->findOrFail($invoice);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'reference' => ['required', 'string', 'max:191'],
            'method' => ['nullable', 'string', 'max:40'],
            'confirmed' => ['accepted'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = $this->billing->recordPayment($invoice, $data);

        try {
            Mail::to($invoice->tenant->email)->send(
                new PaymentRecordedMail($invoice->tenant, $invoice->refresh(), (float) $transaction->amount)
            );
        } catch (\Throwable $exception) {
            Log::warning('SaaS payment receipt mail could not be sent.', [
                'invoice' => $invoice->number,
                'exception' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', trans('saas::app.billing.recorded'));
    }

    /**
     * Flag overdue invoices and move their subscriptions past due.
     */
    public function markOverdue()
    {
        $count = $this->billing->markOverdueInvoices();

        return back()->with('success', trans('saas::app.super-admin.billing.overdue-processed', ['count' => $count]));
    }
}
