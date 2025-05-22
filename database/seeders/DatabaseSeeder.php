<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create(['name' => 'John Doe', 'email' => 'john@doe.com', 'password' => Hash::make('John@123')]);
        User::create(['name' => 'Mike Doe', 'email' => 'mike@doe.com', 'password' => Hash::make('Mike@123')]);

        $users = User::factory(100)->create();

        $campaigns = Campaign::factory(10)->recycle($users)->create();

        $donations = Donation::factory(60)->recycle($users)
            ->recycle($campaigns)
            ->create();

        DonationTransaction::factory(80)->recycle($donations)
            ->create();
    }
}
