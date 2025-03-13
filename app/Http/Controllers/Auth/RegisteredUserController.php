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
use Dotenv\Dotenv;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        if (! config('app.hosted') && config('app.url')) {
            return redirect()->route('login');
        }

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

            $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . preg_replace('/\/sign_up$/', '', $request->getSchemeAndHttpHost()), $envContent);                        
            
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST="' . $request->database_host . '"', $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $request->database_port, $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE="' . $request->database_name . '"', $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME="' . $request->database_username . '"', $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $request->database_password . '"', $envContent);

            if ($request->report_errors) {
                $envContent = preg_replace('/SENTRY_LARAVEL_DSN=.*/', 'SENTRY_LARAVEL_DSN="https://4d5293303b2fc59a89bcc85110f6f031@o4508274803539968.ingest.us.sentry.io/4508845950959616"', $envContent);
            }

            file_put_contents(base_path('.env'), $envContent);

            config(['database.connections.mysql.host' => $request->database_host]);
            config(['database.connections.mysql.port' => $request->database_port]);
            config(['database.connections.mysql.database' => $request->database_name]);
            config(['database.connections.mysql.username' => $request->database_username]);
            config(['database.connections.mysql.password' => $request->database_password]);
            
            Artisan::call('migrate', ['--force' => true]);      
            Artisan::call('storage:link');
        }        

        if (! config('app.hosted') && User::count() > 0) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'America/New_York',
            'language_code' => $request->language_code ?? 'en',
        ]);

        if (! config('app.hosted')) {
            $user->email_verified_at = now();
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user, true);

        return redirect(route('home', absolute: false));
    }
}
