<?php

namespace Tests\Feature;

use App\Models\AdminInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_opens_login_page_first(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_operator_register_returns_operator_view_without_student_fields(): void
    {
        $this->get('/operator/register')
            ->assertOk()
            ->assertSee('Submit for Clearance')
            ->assertDontSee('Full Name')
            ->assertDontSee('Member / Student ID')
            ->assertDontSee('Email Address')
            ->assertDontSee('Department / Faculty')
            ->assertDontSee('Batch / Group')
            ->assertDontSee('Phone Number (SMS & WhatsApp Alerts)')
            ->assertDontSee('Password')
            ->assertDontSee('Confirm Password')
            ->assertDontSee('Register & Enter Portal')
            ->assertDontSee('Already have an account?')
            ->assertDontSee('Join digital queues')
            ->assertDontSee('track live positions')
            ->assertDontSee('Tanvir Hasan')
            ->assertDontSee('student@university.edu');
    }

    public function test_student_register_returns_student_view_without_operator_fields(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Student')
            ->assertDontSee('Employee ID')
            ->assertDontSee('Invite Code');
    }

    public function test_operator_role_session_makes_navbar_register_operator_link(): void
    {
        $this->withSession(['selected_role' => 'operator'])
            ->get('/welcome')
            ->assertSee('/operator/register');
    }

    public function test_register_redirects_for_role_query_and_operator_context(): void
    {
        $this->get('/register?role=operator')
            ->assertRedirect('/operator/register');

        $this->withSession(['selected_role' => 'operator'])
            ->get('/register')
            ->assertRedirect('/operator/register');
    }

    public function test_logged_in_student_cannot_open_operator_register(): void
    {
        $student = User::create([
            'name' => 'Student',
            'email' => 'student@test.local',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->get('/operator/register')
            ->assertRedirect('/student/dashboard');
    }

    public function test_demo_role_switch_remembers_operator_and_login_copy_is_separate(): void
    {
        $this->get('/demo-login/operator')
            ->assertRedirect();

        $this->assertSame('operator', session('selected_role'));

        $this->flushSession();
        $this->get('/operator/login')
            ->assertOk()
            ->assertSee('New officer? Request operator access')
            ->assertSee('/operator/register');

        $this->flushSession();
        $this->get('/login')->assertRedirect('/student/login');
        $this->get('/student/login')
            ->assertOk()
            ->assertSee('New student? Register');
    }

    public function test_admin_register_gate_route_exists(): void
    {
        $this->get('/admin/register')
            ->assertOk()
            ->assertSee('Verify Invite');
    }

    public function test_pending_user_cannot_log_in(): void
    {
        $pending = User::create([
            'name' => 'Pending Officer',
            'email' => 'pending.operator@test.local',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
            'is_active' => false,
            'account_status' => 'Pending',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $pending->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonFragment([
                'Your officer access is under review.',
            ]);
    }

    public function test_role_login_pages_are_separate(): void
    {
        $this->get('/student/login')
            ->assertOk()
            ->assertSee('Sign In as Student');

        $this->get('/operator/login')
            ->assertOk()
            ->assertSee('Sign In as Operator');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Sign In as Admin');
    }

    public function test_wrong_role_cannot_log_in_from_role_page(): void
    {
        $student = User::create([
            'name' => 'Student',
            'email' => 'student-role@test.local',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $student->email,
            'password' => 'password',
            'login_role' => 'ADMIN',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_admin_invite_gate_card_is_visible(): void
    {
        $this->get('/admin/register')
            ->assertOk()
            ->assertSee('Invite Token')
            ->assertSee('Verify Invite')
            ->assertSee('Back to Admin Sign In');
    }

    public function test_valid_admin_invite_opens_full_registration_form(): void
    {
        $token = 'DEMO-ADMIN-INVITE';
        AdminInvite::create([
            'token_hash' => hash('sha256', $token),
            'institutional_mail' => 'invited.admin@queueless.local',
            'tier' => 'Super Admin',
            'managed_service_unit_ids' => null,
            'expires_at' => now()->addDays(7),
        ]);

        $this->post('/admin/register', ['invite_token' => $token])
            ->assertRedirect('/admin/register/' . $token);

        $this->get('/admin/register/' . $token)
            ->assertOk()
            ->assertDontSee('Full Name')
            ->assertDontSee('Member / Student ID')
            ->assertDontSee('Department / Faculty')
            ->assertDontSee('Batch / Group')
            ->assertSee('Institutional Mail')
            ->assertSee('Authority ID')
            ->assertSee('Secure Contact Line')
            ->assertSee('Office Title')
            ->assertSee('Profile Photo')
            ->assertSee('Master Passcode')
            ->assertSee('Generate Strong Passcode')
            ->assertSee('Administrator Review')
            ->assertSee('TOTP Setup')
            ->assertSee('Recovery Codes')
            ->assertSee('TOTP Verification Code')
            ->assertSee($token);
    }

    public function test_admin_registration_requires_strong_passcode_and_accepts_profile_details(): void
    {
        Storage::fake('public');

        $this->post('/admin/register/complete', [
            'admin_name' => 'Campus Administrator',
            'employee_id' => 'ADM-001',
            'institutional_mail' => 'admin.details@queueless.local',
            'duty_contact' => '01700000000',
            'office_title' => 'Registrar',
            'profile_photo' => UploadedFile::fake()->image('administrator.jpg', 300, 300)->size(1024),
            'invite_token' => 'DEMO-ADMIN-INVITE',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
            'totp_code' => '123456',
        ])->assertSessionHasErrors('password');

        $this->post('/admin/register/complete', [
            'admin_name' => 'Campus Administrator',
            'employee_id' => 'ADM-002',
            'institutional_mail' => 'admin.details2@queueless.local',
            'duty_contact' => '01700000001',
            'office_title' => 'Registrar',
            'profile_photo' => UploadedFile::fake()->image('administrator.jpg', 300, 300)->size(1024),
            'invite_token' => 'DEMO-ADMIN-INVITE',
            'password' => 'Strong-A1!Passcode',
            'password_confirmation' => 'Strong-A1!Passcode',
            'totp_code' => '123456',
        ])->assertSessionDoesntHaveErrors('password');
    }

    public function test_invalid_admin_invite_shows_generic_error(): void
    {
        $this->post('/admin/register', ['invite_token' => 'NOT-A-VALID-TOKEN'])
            ->assertSessionHasErrors([
                'invite_token' => 'Invalid or expired invite',
            ]);
    }

    public function test_sixth_admin_invite_attempt_is_blocked(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/admin/register', ['invite_token' => 'bad-' . $attempt])
                ->assertSessionHasErrors('invite_token');
        }

        $this->post('/admin/register', ['invite_token' => 'bad-6'])
            ->assertSessionHasErrors('invite_token');
    }

    public function test_only_one_admin_account_can_be_registered(): void
    {
        User::create([
            'name' => 'Existing Admin',
            'email' => 'existing-admin@queueless.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
            'account_status' => 'Active',
        ]);

        AdminInvite::create([
            'token_hash' => hash('sha256', 'ONE-ADMIN-INVITE'),
            'institutional_mail' => 'second.admin@queueless.local',
            'tier' => 'Super Admin',
            'managed_service_unit_ids' => null,
            'expires_at' => now()->addDays(7),
        ]);

        $this->get('/admin/register')
            ->assertOk()
            ->assertSee('Administrator account already exists')
            ->assertDontSee('Verify Invite');

        $this->post('/admin/register', ['invite_token' => 'ONE-ADMIN-INVITE'])
            ->assertSessionHasErrors('invite_token');
    }
}
