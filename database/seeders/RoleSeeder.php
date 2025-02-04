<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Joueur']);
        Role::create(['name' => 'Coach']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Responsable Coach']);
        Role::create(['name' => 'Administrateur']);
    }
}
