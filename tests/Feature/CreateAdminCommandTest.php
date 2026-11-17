<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_command_creates_admin_with_options(): void
    {
        $this->artisan('admin:create', [
            '--username' => 'ivan_admin',
            '--display-name' => 'Ivan',
            '--password' => 'StrongPass1!',
        ])
            ->expectsOutput('Admin [ivan_admin] created successfully.')
            ->assertExitCode(0);

        $admin = Admin::where('username', 'ivan_admin')->firstOrFail();
        $this->assertSame('Ivan', $admin->display_name);
        $this->assertTrue(Hash::check('StrongPass1!', $admin->password));
    }

    public function test_admin_create_command_rejects_duplicate_username_and_weak_password(): void
    {
        Admin::create([
            'username' => 'existing',
            'password' => Hash::make('StrongPass1!'),
        ]);

        $this->artisan('admin:create', [
            '--username' => 'existing',
            '--password' => 'weak',
        ])->assertExitCode(1);
    }
}
