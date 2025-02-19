<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Rules\NoFakeEmail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Artisan;
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
        if ($request->filled('website')) {
            throw ValidationException::withMessages([
                'email' => __('messages.invalid_request'),
            ]);
        }

        if (! config('app.hosted') && ! config('app.url')) {
            $request->validate([
                'database_host' => ['required', 'string', 'max:255'],
                'database_port' => ['required', 'string', 'max:255'],
                'database_name' => ['required', 'string', 'max:255'],
                'database_username' => ['required', 'string', 'max:255'],
                'database_password' => ['required', 'string', 'max:255'],
            ]);

            // Update database settings in .env file
            $envContent = file_get_contents(base_path('.env'));

            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . preg_replace('/\/sign_up$/', '', $request->getSchemeAndHttpHost()), $envContent);            
            $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=database', $envContent);            
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST="' . $request->database_host . '"', $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $request->database_port, $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE="' . $request->database_name . '"', $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME="' . $request->database_username . '"', $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $request->database_password . '"', $envContent);

            file_put_contents(base_path('.env'), $envContent);

            // Run migrations
            Artisan::call('migrate');            
        }


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class, new NoFakeEmail],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'America/New_York',
            'language_code' => $request->language_code ?? 'en',
        ]);

        event(new Registered($user));

        Auth::login($user, true);

        return redirect(route('home', absolute: false));
    }
}
