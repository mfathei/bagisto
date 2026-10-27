<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Repositories\SuperAdminRepository;
use Webkul\Saas\Services\AuditLogger;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:create-super-admin
                            {--name= : Display name of the Super Admin}
                            {--email= : Sign-in email address}
                            {--role=owner : Platform role: owner, admin or support}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Super Admin account by prompting for credentials; no default password is ever used.';

    /**
     * Execute the console command.
     */
    public function handle(SuperAdminRepository $admins, AuditLogger $audit): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Super Admin name')));

        if ($name === '') {
            $this->components->error('A name is required.');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Email address'))));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('A valid email address is required.');

            return self::FAILURE;
        }

        if ($admins->findByEmail($email)) {
            $this->components->error('A Super Admin with this email address already exists.');

            return self::FAILURE;
        }

        $role = strtolower(trim((string) $this->option('role')));

        if (! in_array($role, ['owner', 'admin', 'support'], true)) {
            $this->components->error('The role must be one of: owner, admin, support.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if (strlen($password) < 12) {
            $this->components->error('The password must contain at least 12 characters.');

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->components->error('The password confirmation does not match.');

            return self::FAILURE;
        }

        $admin = $admins->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'status' => true,
        ]);

        $audit->platform(AuditAction::SuperAdminCreated, [
            'subject' => $admin,
            'description' => 'Super Admin created from the console.',
            'metadata' => ['email' => $admin->email, 'role' => $admin->role],
        ]);

        $this->components->success('Super Admin created.');

        $this->table(
            ['ID', 'Name', 'Email', 'Role'],
            [[$admin->getKey(), $admin->name, $admin->email, $admin->role]]
        );

        return self::SUCCESS;
    }
}
