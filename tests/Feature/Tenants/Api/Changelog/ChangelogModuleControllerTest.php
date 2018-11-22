<?php

namespace Tests\Feature\Tenants\Api\Incidents;

use App\Events\Incidents\IncidentStored;
use App\Mail\Incidents\IncidentCreated;
use App\Mail\Incidents\IncidentDeleted;
use App\Models\Incident;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Config;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Spatie\Permission\Models\Role;
use Tests\BaseTenantTest;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class ChangelogModuleControllerTest.
 *
 * @package Tests\Feature\Tenants\Api
 */
class ChangelogModuleControllerTest extends BaseTenantTest {

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

    public function guest_cannot_list_logs_for_an_specific_module()
    {
        sample_logs();
        $user = factory(User::class)->create();
        $this->actingAs($user,'api');

        $response =  $this->json('GET','/api/v1/changelog');
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
        $this->assertCount(3,$result);

        $this->assertEquals($logs[0]->id,$result[0]->id);
        $this->assertEquals($logs[0]->text,$result[0]->text);
        $this->assertNotNull($result[0]->time);
        $this->assertNotNull($result[0]->human_time);
        $this->assertNotNull($result[0]->timestamp);
        $this->assertEquals($logs[0]->action_type, $result[0]->action_type);
        $this->assertEquals($logs[0]->module, $result[0]->module);
        $this->assertEquals($logs[0]->user_id, $result[0]->user_id);
        $this->assertEquals($logs[0]->user->id, $result[0]->user->id);
        $this->assertEquals($logs[0]->user->name, $result[0]->user->name);
        $this->assertEquals($logs[0]->user->email, $result[0]->user->email);
        $this->assertEquals($logs[0]->user->hashid, $result[0]->user->hashid);
        $this->assertEquals($logs[0]->icon, $result[0]->icon);
        $this->assertEquals($logs[0]->color, $result[0]->color);
    }


    /**
     * @test
     */
    public function can_list_logs_for_an_specific_module()
    {
        $user = factory(User::class)->create();
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $this->actingAs($user,'api');
        $log1 = Log::create([
            'text' => 'Ha creat la incidència TODO_LINK_INCIDENCIA',
            'time' => Carbon::now(),
            'action_type' => 'update',
            'module_type' => 'Incidents',
            'user_id' => $user1->id,
            'icon' => 'home',
            'color' => 'teal'
        ]);
        $log2 = Log::create([
            'text' => 'Ha modificat la incidència TODO_LINK_INCIDENCIA',
            'time' => Carbon::now(),
            'action_type' => 'update',
            'module_type' => 'Incidents',
            'user_id' => $user2->id,
            'icon' => 'home',
            'color' => 'teal'
        ]);
        $log3 = Log::create([
            'text' => 'Ha modificat la incidència TODO_LINK_INCIDENCIA',
            'time' => Carbon::now(),
            'action_type' => 'update',
            'module_type' => 'Incidents',
            'user_id' => $user2->id,
            'icon' => 'home',
            'color' => 'teal'
        ]);
        $logs = [$log1,$log2,$log3];
        $response =  $this->json('GET','/api/v1/changelog');
        $response->assertSuccessful();
        $result = json_decode($response->getContent());
        $this->assertCount(3,$result);

        $this->assertEquals($logs[0]->id,$result[0]->id);
        $this->assertEquals($logs[0]->text,$result[0]->text);
        $this->assertNotNull($result[0]->time);
        $this->assertNotNull($result[0]->human_time);
        $this->assertNotNull($result[0]->timestamp);
        $this->assertEquals($logs[0]->action_type, $result[0]->action_type);
        $this->assertEquals($logs[0]->module, $result[0]->module);
        $this->assertEquals($logs[0]->user_id, $result[0]->user_id);
        $this->assertEquals($logs[0]->user->id, $result[0]->user->id);
        $this->assertEquals($logs[0]->user->name, $result[0]->user->name);
        $this->assertEquals($logs[0]->user->email, $result[0]->user->email);
        $this->assertEquals($logs[0]->user->hashid, $result[0]->user->hashid);
        $this->assertEquals($logs[0]->icon, $result[0]->icon);
        $this->assertEquals($logs[0]->color, $result[0]->color);
    }

}