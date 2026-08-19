<?php

use App\Models\Employee;
use App\Models\User;
use App\Notifications\PasswordResetByAdminNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    seedRoles();
});

function makeEmployeeWithUser(string $email = null): Employee
{
    $email = $email ?? 'emp' . random_int(1000, 9999) . '@test.local';
    $user = User::create([
        'name'                 => 'Test',
        'prenoms'              => 'User',
        'email'                => $email,
        'password'             => Hash::make('OldPassword123!'),
        'contact'              => '0600000000',
        'matricule'            => 'U-' . random_int(1000, 9999),
        'statut'               => 1,
        'must_change_password' => false,
    ]);
    $user->assignRole('user');
    return Employee::create([
        'noms'          => 'Doe',
        'prenoms'       => 'Jane',
        'matricule'     => 'EMP-' . random_int(1000, 9999),
        'email'         => $email,
        'user_id'       => $user->id,
        'salaire_base'  => 500000,
        'type_contrat'  => 'CDI',
        'date_embauche' => now(),
        'statut'        => 1,
    ]);
}

it('la vue edit employé n\'expose plus de champ password', function () {
    actingAsSuperAdmin();
    $emp = makeEmployeeWithUser();
    $resp = $this->get("/rh/employees/{$emp->id}/edit");
    $resp->assertOk();
    $resp->assertDontSee('name="password"', false);
    $resp->assertDontSee('name="password_confirmation"', false);
    $resp->assertSee('Réinitialiser', false);
});

it('un POST de password sur /rh/employees/{id} est ignoré (garde-fou)', function () {
    actingAsSuperAdmin();
    $emp = makeEmployeeWithUser();
    $oldHash = $emp->user->password;

    $this->put("/rh/employees/{$emp->id}", [
        'noms'      => $emp->noms,
        'prenoms'   => $emp->prenoms,
        'email'     => $emp->user->email,
        'matricule' => $emp->matricule,
        'password'  => 'TentativeInjection123!',
        'password_confirmation' => 'TentativeInjection123!',
    ]);

    expect($emp->user->fresh()->password)->toBe($oldHash);
});

it('la vue de reset password est accessible aux admins, refusée aux users', function () {
    $emp = makeEmployeeWithUser();
    actingAsUser();
    $this->get("/rh/employees/{$emp->id}/reset-password")->assertForbidden();
    actingAsSuperAdmin();
    $this->get("/rh/employees/{$emp->id}/reset-password")->assertOk();
});

it('reset password exige confirmation, mot de passe admin et mode', function () {
    actingAsSuperAdmin();
    $emp = makeEmployeeWithUser();

    // Sans mode
    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'admin_password' => 'password',
        'confirmation'   => '1',
    ])->assertSessionHasErrors('mode');

    // Mauvais mot de passe admin
    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'           => 'auto',
        'admin_password' => 'mauvais',
        'confirmation'   => '1',
    ])->assertSessionHasErrors('admin_password');

    // Sans confirmation
    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'           => 'auto',
        'admin_password' => 'password',
    ])->assertSessionHasErrors('confirmation');
});

it('mode auto : génère un mot de passe et envoie un email', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();
    $oldHash = $emp->user->password;

    $resp = $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'           => 'auto',
        'admin_password' => 'password',
        'confirmation'   => '1',
        'motif'          => 'Test auto',
    ]);
    $resp->assertRedirect("/rh/employees/{$emp->id}");
    $resp->assertSessionHas('success');
    $resp->assertSessionMissing('reset_password_otp'); // pas d'OTP affiché si email OK

    $userFresh = $emp->user->fresh();
    expect($userFresh->password)->not->toBe($oldHash);
    expect($userFresh->must_change_password)->toBeTrue();

    // Vérifier que la Notification a bien été envoyée
    Notification::assertSentTo($userFresh, PasswordResetByAdminNotification::class,
        function ($n) use ($admin) {
            expect($n->temporaryPassword)->toBeString()->toHaveLength(16);
            expect($n->adminName)->toContain($admin->name);
            return true;
        }
    );
});

it('mode manuel : utilise le mot de passe saisi par l\'admin', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();
    $nouveau = 'ManuelChoisi123';

    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'                  => 'manuel',
        'password'              => $nouveau,
        'password_confirmation' => $nouveau,
        'admin_password'        => 'password',
        'confirmation'          => '1',
    ])->assertRedirect("/rh/employees/{$emp->id}");

    $userFresh = $emp->user->fresh();
    expect(Hash::check($nouveau, $userFresh->password))->toBeTrue();

    Notification::assertSentTo($userFresh, PasswordResetByAdminNotification::class,
        fn($n) => $n->temporaryPassword === $nouveau
    );
});

it('mode manuel : refuse un mot de passe sans confirmation correcte', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();

    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'                  => 'manuel',
        'password'              => 'NouveauPass123',
        'password_confirmation' => 'AutreChose999',
        'admin_password'        => 'password',
        'confirmation'          => '1',
    ])->assertSessionHasErrors('password');

    // Pas de notification envoyée
    Notification::assertNothingSent();
});

it('mode manuel : refuse un mot de passe trop faible (règles de complexité)', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();

    // Pas de majuscule
    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'                  => 'manuel',
        'password'              => 'tropsimple123',
        'password_confirmation' => 'tropsimple123',
        'admin_password'        => 'password',
        'confirmation'          => '1',
    ])->assertSessionHasErrors('password');

    // Trop court
    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'                  => 'manuel',
        'password'              => 'A1b',
        'password_confirmation' => 'A1b',
        'admin_password'        => 'password',
        'confirmation'          => '1',
    ])->assertSessionHasErrors('password');

    Notification::assertNothingSent();
});

it('mode manuel : exige le champ password (required_if)', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();

    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'           => 'manuel',
        'admin_password' => 'password',
        'confirmation'   => '1',
    ])->assertSessionHasErrors('password');
});

it('reset password réussi : audit log créé avec canal email', function () {
    Notification::fake();
    $admin = actingAsSuperAdmin();
    $admin->update(['password' => Hash::make('password')]);
    $emp = makeEmployeeWithUser();

    $this->post("/rh/employees/{$emp->id}/reset-password", [
        'mode'           => 'auto',
        'admin_password' => 'password',
        'confirmation'   => '1',
        'motif'          => 'Test reset',
    ]);

    $log = \Spatie\Activitylog\Models\Activity::where('log_name', 'password_reset')
        ->where('subject_id', $emp->user->id)
        ->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($admin->id);
    expect($log->properties['motif'])->toBe('Test reset');
    expect($log->properties['mode'])->toBe('auto');
    expect($log->properties['email_sent'])->toBeTrue();
    expect($log->properties['email_to'])->toBe($emp->user->email);
});

it('un user avec must_change_password est redirigé vers la page de changement', function () {
    $emp = makeEmployeeWithUser();
    $emp->user->update([
        'must_change_password' => true,
        'password'             => Hash::make('TempPass123!'),
    ]);
    $this->actingAs($emp->user);
    $this->get('/dashboard')->assertRedirect(route('password.force-change.show'));
});

it('après changement de mot de passe, must_change_password passe à false', function () {
    $emp = makeEmployeeWithUser();
    $emp->user->update([
        'must_change_password' => true,
        'password'             => Hash::make('TempPass123!'),
    ]);
    $this->actingAs($emp->user);

    $this->post('/password/force-change', [
        'current_password'      => 'TempPass123!',
        'password'              => 'NouveauStrong456!',
        'password_confirmation' => 'NouveauStrong456!',
    ])->assertRedirect(route('dashboard'));

    $u = $emp->user->fresh();
    expect($u->must_change_password)->toBeFalse();
    expect($u->password_changed_at)->not->toBeNull();
    expect(Hash::check('NouveauStrong456!', $u->password))->toBeTrue();
});

it('force-change refuse un mot de passe identique à l\'ancien', function () {
    $emp = makeEmployeeWithUser();
    $emp->user->update([
        'must_change_password' => true,
        'password'             => Hash::make('TempPass123!'),
    ]);
    $this->actingAs($emp->user);

    $this->post('/password/force-change', [
        'current_password'      => 'TempPass123!',
        'password'              => 'TempPass123!',
        'password_confirmation' => 'TempPass123!',
    ])->assertSessionHasErrors('password');
});
