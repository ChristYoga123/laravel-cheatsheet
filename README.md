# About

Queue di Laravel adalah fitur yang memungkinkan aplikasi untuk menunda tugas-tugas yang memerlukan waktu pemrosesan yang lama, seperti pengiriman email, pemrosesan gambar, atau tugas-tugas lainnya. Dengan menggunakan Queue, aplikasi dapat terus berjalan tanpa menunggu tugas-tugas tersebut selesai diproses, dan hasilnya dapat dikirimkan ke pengguna pada waktu yang tepat. Laravel menyediakan driver Queue yang dapat diintegrasikan dengan berbagai layanan antrian, seperti Redis, Beanstalkd, atau database SQL.

Untuk driver yang akan digunakan adalah database

### Steps

1. Buat table job dulu untuk menyimpan queue lalu migrate

```php artisan queue:table```
```php artisan migrate```

2. Buat job task

```php artisan make:job <NAMAJOB>```

3. Masukkan task yang akan dibuat async ke dalam job

File app/Jobs/<NAMAJOB.php>

```
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
        // masukkan tugas yang akan dihandle secara async di method handle(). Jika ingin memberikan construct method juga dipersilahkan
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
```

<i>Testing create 10000 data user dengan factory faker</i>

Di file routes/web.php

```
<?php

use App\Jobs\StoreUserData;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // Store Data Queue Logic
    $start = microtime(true);
    dispatch(new StoreUserData()); // memanggil job untuk dijalankan secara async
    $end = microtime(true);
    $durasi = $end - $start;
    return "<h1>Halaman diproses dalam ". $durasi . ' detik</h1>';
});
```

4. Jalankan queuenya

```php artisan queue:work```

5. Queue Create data berhasil diterapkan

Hasilnya sekalipun website kita dipaksa input 10000 data user, website kita tidak perlu menunggu proses memasukkannya selesai tetapi job yang lama masuk ke queue dan website akan menjalankan code di bawahnya.

<img src="/queue-result.png" width="500px">

### REFERENSI: Programming Di Rumahrafif => https://www.youtube.com/watch?v=U2g_mNL1yc8