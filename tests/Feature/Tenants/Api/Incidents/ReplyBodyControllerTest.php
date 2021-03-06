<?php

namespace Tests\Feature\Tenants\Api;

use App\Events\Incidents\IncidentReplyUpdated;
use App\Models\Incident;
use App\Models\Reply;
use App\Models\User;
use Config;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\BaseTenantTest;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class ReplyBodyControllerTest.
 *
 * @package Tests\Feature\Tenants\Api
 */
class ReplyBodyControllerTest extends BaseTenantTest {

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
    public function incidents_manager_can_update_reply_body() {
        $user = factory(User::class)->create([
            'name' => 'Carles Puigdemont'
        ]);
        $role = Role::firstOrCreate(['name' => 'IncidentsManager']);
        Config::set('auth.providers.users.model', User::class);
        $user->assignRole($role);
        $incident = Incident::create([
            'subject' => 'No funciona res Aula 2',
            'description' => 'Bla bla bla'
        ]);
        $this->actingAs($user,'api');

        $replyUser = factory(User::class)->create([
            'name' => 'Oriol Junqueras'
        ]);
        $reply = Reply::create([
            'body' => 'Ja ho hem solucionat tot',
            'user_id' => $replyUser->id
        ]);
        $incident->addReply($reply);
        Event::fake();
        $response = $this->json('PUT','/api/v1/replies/' . $reply->id . '/body',[
            'body' => 'Perdo no hem solucionat res'
        ]);
        $response->assertSuccessful();
        Event::assertDispatched(IncidentReplyUpdated::class,function ($event) use ($incident){
            return $event->incident->is($incident) && $event->oldReply->body === 'Ja ho hem solucionat tot' && $event->reply->body === 'Perdo no hem solucionat res';
        });
        $result = json_decode($response->getContent());
        $reply = $reply->fresh();
        $this->assertEquals($result->body,$reply->body);
        $this->assertEquals($result->id,$reply->id);

        $reply = $reply->fresh();
        $this->assertEquals($reply->body, 'Perdo no hem solucionat res');
    }

    /**
     * @test
     */
    public function incidents_manager_can_update_reply_body_validation() {
        $user = factory(User::class)->create([
            'name' => 'Carles Puigdemont'
        ]);
        $role = Role::firstOrCreate(['name' => 'IncidentsManager']);
        Config::set('auth.providers.users.model', User::class);
        $user->assignRole($role);

        $this->actingAs($user,'api');

        $replyUser = factory(User::class)->create([
            'name' => 'Oriol Junqueras'
        ]);
        $reply = Reply::create([
            'body' => 'Ja ho hem solucionat tot',
            'user_id' => $replyUser->id
        ]);
        $response = $this->json('PUT','/api/v1/replies/' . $reply->id . '/body');
        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function regular_user_cannot_update_reply_body_validation() {
        $user = factory(User::class)->create([
            'name' => 'Carles Puigdemont'
        ]);
        $this->actingAs($user,'api');

        $replyUser = factory(User::class)->create([
            'name' => 'Oriol Junqueras'
        ]);
        $reply = Reply::create([
            'body' => 'Ja ho hem solucionat tot',
            'user_id' => $replyUser->id
        ]);
        $response = $this->json('PUT','/api/v1/replies/' . $reply->id . '/body');
        $response->assertStatus(403);
    }

}
