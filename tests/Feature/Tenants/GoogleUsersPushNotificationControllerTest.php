<?php

namespace Tests\Feature\Tenants;

use App\Events\GoogleUserNotificationReceived;
use Event;
use Illuminate\Contracts\Console\Kernel;
use Mail;
use Tests\BaseTenantTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\GoogleUserNotificationReceived as GoogleUserNotificationReceivedMail;

/**
 * Class GoogleSuiteUsersPushNotificationControllerTest.
 *
 * @package Tests\Feature
 */
class GoogleSuiteUsersPushNotificationControllerTest extends BaseTenantTest
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

    /**
     * @test
     * @group working
     */
    public function can_receive_google_suite_users_push_notifications()
    {
        Event::fake();
//        $this->withoutExceptionHandling();
        $response = $this->post('/gsuite/notifications',[],[
            'X-Goog-resource-state' => 'sync',
            'X-Goog-inventat' => 'PROVA'
        ]);

        $response->assertSuccessful();

        Event::assertDispatched(GoogleUserNotificationReceived::class, function ($e) {
            return get_class($e->request) === 'Illuminate\Http\Request';
        });

    }
}