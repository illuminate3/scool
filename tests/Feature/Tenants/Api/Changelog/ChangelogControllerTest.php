<?php

namespace Tests\Feature\Tenants\Api\Incidents;

use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BaseTenantTest;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class ChangelogControllerTest.
 *
 * @package Tests\Feature\Tenants\Api
 */
class ChangelogControllerTest extends BaseTenantTest {

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

    /**
     * @test
     */
    public function can_list_logs()
    {
        $logs = sample_logs();
        $user = factory(User::class)->create();
        $role = Role::firstOrCreate(['name' => 'ChangelogManager']);
        Config::set('auth.providers.users.model', User::class);
        $user->assignRole($role);
        $this->actingAs($user,'api');

        $response =  $this->json('GET','/api/v1/changelog');
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
//        dump($result);
        $this->assertCount(4,$result);

        $this->assertEquals($logs[0]->id,$result[0]->id);
        $this->assertEquals($logs[0]->text,$result[0]->text);
        $this->assertNotNull($result[0]->time);
        $this->assertNotNull($result[0]->human_time);
        $this->assertNotNull($result[0]->timestamp);
        $this->assertEquals($logs[0]->action_type, $result[0]->action_type);
        $this->assertEquals($logs[0]->module, $result[0]->module);
        $this->assertEquals($logs[0]->user_id, $result[0]->user_id);
        $this->assertEquals($logs[0]->user->name, $result[0]->user_name);
        $this->assertEquals($logs[0]->user->email, $result[0]->user_email);
        $this->assertEquals($logs[0]->user->hashid, $result[0]->user_hashid);
        $this->assertEquals($logs[0]->icon, $result[0]->icon);
        $this->assertEquals($logs[0]->color, $result[0]->color);
    }


    /**
     * @test
     */
    public function guest_cannot_list_logs()
    {
        $response =  $this->json('GET','/api/v1/changelog');
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function regular_user_cannot_list_logs()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user,'api');
        $response =  $this->json('GET','/api/v1/changelog');
        $response->assertStatus(403);
    }
}
