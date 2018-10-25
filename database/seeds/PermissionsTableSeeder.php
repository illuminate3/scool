<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Gate::abilities() as $abilityKey => $abilityValue) {
            Permission::firstOrCreate(['name' => $abilityKey]);
        }
    }
}