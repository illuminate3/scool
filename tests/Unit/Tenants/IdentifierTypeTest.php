<?php

namespace Tests\Unit\Tenants;

use App\Models\IdentifierType;
use App\Models\User;
use Config;
use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class IdentifierTypeTest.
 *
 * @package Tests\Unit
 */
class IdentifierTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        Config::set('auth.providers.users.model',User::class);
    }

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
    public function find_by_name()
    {
        $this->assertNull(IdentifierType::findByName('NIF'));

        $nif = IdentifierType::create([
            'name' => 'NIF'
        ]);

        $this->assertTrue($nif->is(IdentifierType::findByName('NIF')));

    }

}
