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
        if (! config('app.hosted') && config('app.url') && ! config('app.is_testing')) {
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

            $baseUrl = $request->getSchemeAndHttpHost();
            $basePath = $request->getBaseUrl(); // Detects "/public" if present
            $url = rtrim($baseUrl . $basePath, '/');

            // Update database settings in .env file
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            // Sanitize input values to prevent injection
            $sanitizedHost = addslashes($request->database_host);
            $sanitizedPort = (int) $request->database_port;
            $sanitizedName = addslashes($request->database_name);
            $sanitizedUsername = addslashes($request->database_username);
            $sanitizedPassword = addslashes($request->database_password);
            $sanitizedUrl = addslashes($url);

            $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL="' . $sanitizedUrl . '"', $envContent);                        
            
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST="' . $sanitizedHost . '"', $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $sanitizedPort, $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE="' . $sanitizedName . '"', $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME="' . $sanitizedUsername . '"', $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $sanitizedPassword . '"', $envContent);

            if ($request->report_errors) {
                $envContent = preg_replace('/REPORT_ERRORS=.*/', 'REPORT_ERRORS=true', $envContent);
            }

            // Write to temporary file first, then move to prevent corruption
            $tempFile = $envPath . '.tmp';
            if (file_put_contents($tempFile, $envContent) === false) {
                throw new \Exception('Failed to write configuration file');
            }
            
            if (!rename($tempFile, $envPath)) {
                unlink($tempFile);
                throw new \Exception('Failed to update configuration file');
            }

            file_put_contents($envPath, $envContent);

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
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'America/New_York',
            'language_code' => $request->language_code ?? 'en',
        ]);

        if (! config('app.hosted') || config('app.is_testing')) {
            $user->email_verified_at = now();
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user, true);

        return redirect(route('home', absolute: false));
    }
}
