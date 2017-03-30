<?php

use Illuminate\Database\Migrations\Migration;
use LaravelEnso\Core\app\Models\Owner;
use LaravelEnso\Core\app\Models\Role;
use LaravelEnso\Core\app\Models\User;

class InsertDefaultUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::transaction(function () {
            $user = new User();
            $user->password = '$2y$10$06TrEefmqWBO7xghm2PUzeF/O0wcawFUv8TKYq.NF6Dsa0Pnmd/F2';
            $user->email = 'admin@login.com';
            $user->first_name = 'Admin';
            $role = Role::whereName('admin')->first();
            $user->role_id = $role->id;
            $owner = Owner::whereName('Admin')->first();
            $user->owner_id = $owner->id;
            $user->is_active = true;
            $user->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('users')->delete();
    }
}