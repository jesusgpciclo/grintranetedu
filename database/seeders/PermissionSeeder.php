<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\PermissionManagerService;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermissionManagerService::applyDefaultAssignments();
    }
}
