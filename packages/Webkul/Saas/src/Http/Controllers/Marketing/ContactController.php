<?php

namespace Webkul\Saas\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\ContactInquiry;
use Webkul\Saas\Enums\ContactInquiryStatus;
use Webkul\Saas\Mail\ContactInquiryMail;
use Webkul\Saas\Repositories\ContactInquiryRepository;
use Webkul\Saas\Services\MarketingContent;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        MarketingContent $content,
        protected ContactInquiryRepository $inquiries
    ) {
        parent::__construct($content);
    }

    /**
     * Display the contact form.
     */
    public function show()
    {
        return view('saas::marketing.pages.contact', [
            'meta' => $this->meta(
                trans('saas::marketing.meta.contact_title'),
                trans('saas::marketing.meta.contact_description')
            ),
            'site' => $this->site(),
            'inquiryTypes' => config('saas.marketing.inquiry_types', []),
        ]);
    }

    /**
     * Persist a contact inquiry and notify the platform team.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'inquiry_type' => ['required', 'string', Rule::in(config('saas.marketing.inquiry_types', []))],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'privacy' => ['accepted'],
        ]);

        if (trim((string) $request->input('website', '')) !== '') {
            throw ValidationException::withMessages([
                'message' => trans('saas::marketing.contact.spam'),
            ]);
        }

        $inquiry = $this->inquiries->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'inquiry_type' => $data['inquiry_type'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'status' => ContactInquiryStatus::New->value,
        ]);

        $this->notify($inquiry);

        return redirect($this->path('saas.marketing.contact'))
            ->with('success', trans('saas::marketing.contact.success'));
    }

    /**
     * Notify configured recipients without failing the stored inquiry.
     */
    protected function notify(ContactInquiry $inquiry): void
    {
        $recipients = $this->content->contactRecipients();

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new ContactInquiryMail($inquiry));

            $inquiry->forceFill(['notified_at' => now()])->save();
        } catch (\Throwable $exception) {
            Log::warning('SaaS contact inquiry notification failed.', [
                'inquiry' => $inquiry->uuid,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
