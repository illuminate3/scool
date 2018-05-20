<?php

namespace Tests\Feature\Tenants;

use App\Models\Family;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffType;
use App\Models\User;
use Config;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Permission\Models\Role;
use Tests\BaseTenantTest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StaffControllerTest.
 *
 * @package Tests\Feature\Tenants
 */
class StaffControllerTest extends BaseTenantTest
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
    public function show_staff_management()
    {
        $staffManager = create(User::class);
        $this->actingAs($staffManager);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);

        $response = $this->get('/staff');

        $response->assertSuccessful();
        $response->assertViewIs('tenants.staff.show');
        $response->assertViewHas('staff');
        $response->assertViewHas('staffTypes');
        $response->assertViewHas('specialties');
        $response->assertViewHas('families');
        $response->assertViewHas('users');
    }

    /** @test */
    public function regular_user_not_authorized_to_show_staff_management()
    {
        $user = create(User::class);
        $this->actingAs($user);
        Config::set('auth.providers.users.model', User::class);
        $response = $this->get('/staff');
        $response->assertStatus(403);
    }

    /** @test */
    public function add_staff()
    {
        initialize_staff_types();
        initialize_forces();
        initialize_families();
        initialize_specialities();
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $this->assertCount(0, Staff::all());
        $response = $this->json('POST','/api/v1/staff', [
            'code' => '040',
            'type' => 'Professor/a',
            'family' => 1,
            'specialty' => 1,
            'holder' => 1,
            'notes' => 'Prova a veure que tal'
        ]);
        $response->assertSuccessful();

        $this->assertCount(1, Staff::all());

        $staff = Staff::find(1);
        $this->assertEquals('040', $staff->code);
        $this->assertEquals('Prova a veure que tal',$staff->notes);
        $this->assertEquals('Professor/a', StaffType::find($staff->type_id)->name);
        $this->assertEquals(1, $staff->user_id);
        $this->assertEquals(1, $staff->family_id);
        $this->assertEquals('Sanitat', Family::find($staff->family_id)->name);
        $this->assertEquals('Processos diagnòstics clínics i productes ortoprotètics', Specialty::find($staff->specialty_id)->name);

    }

    /** @test */
    public function add_staff_validation()
    {
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $response = $this->json('POST','/api/v1/staff', [

        ]);
        $result = json_decode($response->getContent());
        $response->assertStatus(422);
        $this->assertEquals('The given data was invalid.',$result->message);
        $this->assertEquals('El camp code és obligatori.',$result->errors->code[0]);
        $this->assertEquals('El camp type és obligatori.',$result->errors->type[0]);

        $response = $this->json('POST','/api/v1/staff', [
          'code' => '040',
          'type' => 'Professor/a'
        ]);
        $result = json_decode($response->getContent());
        $response->assertStatus(422);
        $this->assertEquals('The given data was invalid.',$result->message);
        $this->assertEquals('El camp family és obligatori quan type és Professor/a.',$result->errors->family[0]);
        $this->assertEquals('El camp specialty és obligatori quan type és Professor/a.',$result->errors->specialty[0]);


    }

    /** @test */
    public function user_cannot_add_staff()
    {
        $user = create(User::class);
        $this->actingAs($user,'api');
        $response = $this->json('POST','/api/v1/staff', [

        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function remove_staff()
    {
        $this->withoutExceptionHandling();
        initialize_staff_types();
        initialize_forces();
        initialize_families();
        initialize_specialities();
        $staffManager = create(User::class);
        $role = Role::firstOrCreate(['name' => 'StaffManager']);
        Config::set('auth.providers.users.model', User::class);
        $staffManager->assignRole($role);
        $this->actingAs($staffManager,'api');

        $staff = Staff::create([
            'code' => '040',
            'type_id' => $type = StaffType::findByName('Professor/a')->id,
            'specialty_id' => 1,
            'family_id' => 1,
            'user_id' => 1,
            'notes' => 'bla bla bla'
        ]);

        $this->assertCount(1, Staff::all());
        $response = $this->json('DELETE','/api/v1/staff/' . $staff->id);
        $response->assertSuccessful();

        $this->assertCount(0, Staff::all());
        $result = json_decode($response->getContent());

        $this->assertEquals('040',$result->code);
        $this->assertEquals($type,$result->type_id);
        $this->assertEquals(1,$result->specialty_id);
        $this->assertEquals(1,$result->family_id);
        $this->assertEquals('bla bla bla',$result->notes);

        $result =  json_encode($response->getContent());

    }

    /** @test */
    public function user_cannot_remove_staff()
    {
        initialize_staff_types();
        initialize_forces();
        initialize_families();
        initialize_specialities();
        $user = create(User::class);
        $this->actingAs($user,'api');

        $staff = Staff::create([
            'code' => '040',
            'type_id' => StaffType::findByName('Professor/a')->id,
            'specialty_id' => 1,
            'family_id' => 1,
            'user_id' => 1,
            'notes' => 'bla bla bla'
        ]);

        $response = $this->json('DELETE','/api/v1/staff/' . $staff->id);
        $response->assertStatus(403);
    }
}
