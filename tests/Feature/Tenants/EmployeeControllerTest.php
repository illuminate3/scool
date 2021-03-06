<?php

namespace Tests\Feature\Tenants;

use App\Models\Employee;
use App\Models\Job;
use App\Models\User;
use Config;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Permission\Models\Role;
use Tests\BaseTenantTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class EmployeeControllerTest.
 *
 * @package Tests\Feature\Tenants
 */
class EmployeeControllerTest extends BaseTenantTest
{
    use RefreshDatabase;

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshInMemoryDatabase()
    {
        $this->artisan('migrate',[
            '--path' => 'database/migrations/tenant'
        ]);

        $this->app[Kernel::class]->setArtisan(null);
    }


    /** @test */
    public function add_holder()
    {
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $user = factory(User::class)->create();
        $job = Job::create([
            'code' => 'CODE',
            'type_id' => 1,
            'specialty_id' => 1,
            'family_id' => 1,
            'order' => 1
        ]);
        $response = $this->json('POST','/api/v1/employee', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'holder' => true
        ]);
        $response->assertSuccessful();
        $this->assertTrue($user->is($job->users()->first()));
        $this->assertTrue($user->is($job->holders()->first()));
        $this->assertNull($job->substitutes()->first());
        $this->assertDatabaseHas('employees',[
            'user_id' => $user->id,
            'job_id' => $job->id,
            'holder' => 1
        ]);
    }

    /** @test */
    public function add_substitute()
    {
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $user = factory(User::class)->create();
        $job = Job::create([
            'code' => 'CODE',
            'type_id' => 1,
            'specialty_id' => 1,
            'family_id' => 1,
            'order' => 1
        ]);
        $response = $this->json('POST','/api/v1/employee', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'holder' => false,
            'start_at' => '2016-02-15'
        ]);
        $response->assertSuccessful();
        $this->assertTrue($user->is($job->users()->first()));
        $this->assertTrue($user->is($job->substitutes()->first()));
        $this->assertNull($job->holders()->first());
        $this->assertDatabaseHas('employees',[
            'user_id' => $user->id,
            'job_id' => $job->id,
            'holder' => 0,
            'start_at' => '2016-02-15 00:00:00'
        ]);
    }

    /** @test */
    public function add_employee_validation()
    {
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        factory(User::class)->create();
        Job::create([
            'code' => 'CODE',
            'type_id' => 1,
            'specialty_id' => 1,
            'family_id' => 1,
            'order' => 1
        ]);
        $response = $this->json('POST','/api/v1/employee', []);
        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_add_employee()
    {
        $user = create(User::class);
        $this->actingAs($user,'api');

        factory(User::class)->create();
        Job::create([
            'code' => 'CODE',
            'type_id' => 1,
            'specialty_id' => 1,
            'family_id' => 1,
            'order' => 1
        ]);
        $response = $this->json('POST','/api/v1/employee', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function remove_employee()
    {
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $employee= Employee::create([
            'user_id' => 1,
            'job_id' => 1,
            'holder' => true
        ]);
        $response = $this->json('DELETE','/api/v1/employee/' . $employee->id);
        $response->assertSuccessful();
    }

    /** @test */
    public function user_cannot_remove_employee()
    {
        $user = create(User::class);
        $this->actingAs($user,'api');

        $employee= Employee::create([
            'user_id' => 1,
            'job_id' => 1,
            'holder' => true
        ]);
        $response = $this->json('DELETE','/api/v1/employee/' . $employee->id);
        $response->assertStatus(403);
    }

}
