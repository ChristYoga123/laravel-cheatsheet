# About

Queue di Laravel adalah fitur yang memungkinkan aplikasi untuk menunda tugas-tugas yang memerlukan waktu pemrosesan yang lama, seperti pengiriman email, pemrosesan gambar, atau tugas-tugas lainnya. Dengan menggunakan Queue, aplikasi dapat terus berjalan tanpa menunggu tugas-tugas tersebut selesai diproses, dan hasilnya dapat dikirimkan ke pengguna pada waktu yang tepat. Laravel menyediakan driver Queue yang dapat diintegrasikan dengan berbagai layanan antrian, seperti Redis, Beanstalkd, atau database SQL.

Untuk driver yang akan digunakan adalah database

### Steps

1. Buat table job dulu untuk menyimpan queue lalu migrate

```php artisan queue:table```
```php artisan migrate```

2. Instalasi Breeze untuk fitur Register

Docs: https://laravel.com/docs/9.x/starter-kits

3. Buat job

```php artisan make:job <NAMAJOB>```

4. Masukkan logika Register dari Breeze ke dalam job

Di file ```RegisteredUser.php```:

```
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\UserRegister;
use App\Mail\AfterRegister;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->dispatch(new UserRegister($user)); // mail memerlukan waktu yang sangat lama bahkan bisa sampai 5 detik ke atas maka mail diletakkan di job
        //Mail::to($user->email)->send(new AfterRegister($user));

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

```

Di file ``` <NAMAJOB>.php ```

```
<?php

namespace App\Jobs;

use App\Mail\AfterRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class UserRegister implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user; // ambil nilai dari controller lalu isi prop user untuk mendapatkan email untuk keperluan MAIL
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new AfterRegister($this->user));
    }
}

```
5. Konfig file .env dengan driver email. Saya menggunakan Mailtrap

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx
MAIL_ENCRYPTION=xxxxxx
MAIL_FROM_ADDRESS="testqueue@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

6. Jalankan queue

```php artisan queue:work```

Hasilnya adalah ketika kita melakukan Login, kita bisa langsung dengan cepat diarahkan ke halaman dashboard tanpa menunggu user mendapat email terlebih dahulu

1. Sebelum Queue

<img src="/public/before_queue.png" width="500px"/>

2. Setelah Queue

<img src="/public/after_queue.png" width="500px"/>