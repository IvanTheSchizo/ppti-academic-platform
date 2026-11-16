<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_guests_to_login_and_authenticated_admins_to_students(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $admin = Admin::create([
            'username' => 'admin_home',
            'password' => Hash::make('Password1!'),
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect(route('students.index'));
    }
}
