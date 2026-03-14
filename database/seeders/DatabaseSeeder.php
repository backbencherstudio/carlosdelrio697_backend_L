<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'mobile' => '01700000000',
            'department' => 'IT',
            'password' => '11111111',
        ]);

        PermissionGroup::create(['group_name' => 'User Management']);

        Permission::insert([
            [
                'permissions_name' => 'View',
                'permission_type' => 'view',
                'group_name' => 'User Management',
                'guard_name' => 'web',
                'route_name' => 'user.view',
            ],
        ]);

        $admin = Role::create(['name' => 'admin']);

        $admin->permissions()->sync(Permission::pluck('id'));

        $user = User::first();
        DB::table('model_has_roles')->insert([
            'role_id' => $admin->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }
}
