<?php

namespace App\Jobs;

use Faker\Factory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class StoreUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $faker = Factory::create();
        $user_ammount = 10000;
        for($i = 1; $i <= $user_ammount; $i++)
        {
            $data = [
                "name" => $faker->name(),
                "email" => $faker->unique()->email(),
                "password" => bcrypt("password")    
            ];

            User::create($data);
        }
    }
}
