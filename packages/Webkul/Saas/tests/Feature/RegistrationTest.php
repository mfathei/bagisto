<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Mail\TenantVerificationMail;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\PlatformSettings;
use Webkul\Saas\Services\TenantManager;
use Webkul\User\Repositories\AdminRepository;

/**
 * Build a valid public registration payload.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function saasRegistrationPayload(array $overrides = []): array
{
    $suffix = Str::lower(Str::random(8));

    return array_merge([
        'name' => 'Registered Store',
        'slug' => 'reg-'.$suffix,
        'owner_name' => 'Registered Owner',
        'owner_email' => 'owner-'.$suffix.'@example.com',
        'owner_password' => 'correct-horse-battery',
        'owner_password_confirmation' => 'correct-horse-battery',
        'email' => 'contact-'.$suffix.'@example.com',
        'timezone' => 'UTC',
        'locale' => 'en',
        'currency' => 'USD',
        'terms' => true,
    ], $overrides);
}

beforeEach(function () {
    $settings = app(PlatformSettings::class);

    $settings->set('registration_enabled', true, 'boolean');
    $settings->set('require_email_verification', true, 'boolean');
    $settings->set('require_approval', true, 'boolean');
});

// ==== Registration ====

it('registers a pending tenant and emails its owner', function () {
    Mail::fake();

    $plan = $this->createPlan();
    $payload = saasRegistrationPayload(['plan_id' => $plan->getKey()]);

    $this->post(route('saas.registration.store'), $payload)
        ->assertRedirect(route('saas.registration.pending'));

    $tenant = app(TenantRepository::class)->findBySlug($payload['slug']);
    $owner = app(AdminRepository::class)->findOneByField('email', $payload['owner_email']);
    $membership = app(TenantUserRepository::class)->findMembership($tenant->getKey(), $owner->getKey());

    expect($tenant)->not->toBeNull()
        ->and($tenant->status)->toBe(TenantStatus::Pending)
        ->and($tenant->email_verified_at)->toBeNull()
        ->and($tenant->approved_at)->toBeNull()
        ->and((int) $tenant->plan_id)->toBe($plan->getKey())
        ->and($owner)->not->toBeNull()
        ->and((int) $owner->tenant_id)->toBe($tenant->getKey())
        ->and($membership)->not->toBeNull()
        ->and($membership->role)->toBe(MembershipRole::Owner);

    Mail::assertSent(TenantVerificationMail::class, fn (TenantVerificationMail $mail) => $mail->hasTo($payload['owner_email']));
});

it('rejects a registration that reuses an owner email', function () {
    $plan = $this->createPlan();
    $first = saasRegistrationPayload(['plan_id' => $plan->getKey()]);

    $this->post(route('saas.registration.store'), $first)
        ->assertRedirect(route('saas.registration.pending'));

    $second = saasRegistrationPayload([
        'plan_id' => $plan->getKey(),
        'owner_email' => $first['owner_email'],
    ]);

    $this->post(route('saas.registration.store'), $second)
        ->assertSessionHasErrors('owner_email');

    expect(app(TenantRepository::class)->findBySlug($second['slug']))->toBeNull();
});

it('rejects a registration that reuses a slug', function () {
    $plan = $this->createPlan();
    $first = saasRegistrationPayload(['plan_id' => $plan->getKey()]);

    $this->post(route('saas.registration.store'), $first)
        ->assertRedirect(route('saas.registration.pending'));

    $second = saasRegistrationPayload([
        'plan_id' => $plan->getKey(),
        'slug' => $first['slug'],
    ]);

    $this->post(route('saas.registration.store'), $second)
        ->assertSessionHasErrors('slug');
});

it('rejects a weak owner password', function () {
    $plan = $this->createPlan();

    $payload = saasRegistrationPayload([
        'plan_id' => $plan->getKey(),
        'owner_password' => 'short',
        'owner_password_confirmation' => 'short',
    ]);

    $this->post(route('saas.registration.store'), $payload)
        ->assertSessionHasErrors('owner_password');
});

// ==== Email Verification ====

it('records verification through a signed link without activating the tenant', function () {
    $tenant = app(TenantManager::class)->create([
        'name' => 'Verifying Store',
        'slug' => 'verify-'.Str::lower(Str::random(8)),
        'owner_name' => 'Verifying Owner',
        'owner_email' => 'verify-'.Str::lower(Str::random(8)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan()->getKey(),
    ])['tenant'];

    $url = app(TenantManager::class)->verificationUrl($tenant);

    $this->get($url)->assertRedirect(route('saas.registration.pending'));

    $tenant->refresh();

    expect($tenant->email_verified_at)->not->toBeNull()
        ->and($tenant->approved_at)->toBeNull()
        ->and($tenant->status)->toBe(TenantStatus::Pending);
});

it('rejects an unsigned verification link', function () {
    $tenant = app(TenantManager::class)->create([
        'name' => 'Unsigned Store',
        'slug' => 'unsigned-'.Str::lower(Str::random(6)),
        'owner_name' => 'Unsigned Owner',
        'owner_email' => 'unsigned-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan()->getKey(),
    ])['tenant'];

    $this->get(route('saas.registration.verify', ['tenant' => $tenant->uuid]))
        ->assertForbidden();

    expect($tenant->refresh()->email_verified_at)->toBeNull();
});

// ==== Abuse Protection ====

it('rate limits repeated registration submissions', function () {
    Mail::fake();

    $plan = $this->createPlan();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('saas.registration.store'), saasRegistrationPayload(['plan_id' => $plan->getKey()]))
            ->assertRedirect(route('saas.registration.pending'));
    }

    $this->post(route('saas.registration.store'), saasRegistrationPayload(['plan_id' => $plan->getKey()]))
        ->assertStatus(429);
});
