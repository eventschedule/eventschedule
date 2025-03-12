<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Event;
use App\Models\EventRole;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;

class Translate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate {--role_id= : Translate only a specific role by ID} {--event_id= : Translate only a specific event by ID} {--event_slug= : Translate only a specific event by slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate non-English data to English';

    public function handle()
    {
        if (! config('services.google.gemini_key')) {
            $this->info('No Gemini API key found, skipping...');
            return;
        }

        $roleId = $this->option('role_id');
        $eventId = $this->option('event_id');
        $eventSlug = $this->option('event_slug');

        // Decode IDs if they are encoded strings
        if ($roleId && !is_numeric($roleId)) {
            $roleId = UrlUtils::decodeId($roleId);
            $this->info("Decoded role ID: $roleId");
        }

        if ($eventId && !is_numeric($eventId)) {
            $eventId = UrlUtils::decodeId($eventId);
            $this->info("Decoded event ID: $eventId");
        }

        // Resolve event_slug to event_id if provided
        if ($eventSlug && !$eventId) {
            $event = Event::where('slug', $eventSlug)->first();
            if ($event) {
                $eventId = $event->id;
                $this->info("Resolved event slug '$eventSlug' to event ID: $eventId");
            } else {
                $this->error("No event found with slug: $eventSlug");
                return;
            }
        }

        if ($roleId) {
            $this->translateRoles($roleId);
            $this->translateCuratorEvents(null, $roleId);
        } else if ($eventId) {
            $this->translateEvents($eventId);
            $this->translateCuratorEvents($eventId);
        } else {
            $this->translateRoles();
            $this->translateEvents();
            $this->translateCuratorEvents();
        }
    }

    public function translateRoles($roleId = null)
    {
        $this->info('Starting translation of roles...');

        // Get all roles that don't have English translations
        $query = Role::where(function($query) {
                $query->whereNotNull('name')
                      ->whereNull('name_en');
            })
            ->orWhere(function($query) {
                $query->whereNotNull('description')
                      ->whereNull('description_en');
            })
            ->orWhere(function($query) {
                $query->whereNotNull('address1')
                      ->whereNull('address1_en');
            })
            ->orWhere(function($query) {
                $query->whereNotNull('city')
                      ->whereNull('city_en');
            })
            ->orWhere(function($query) {
                $query->whereNotNull('state')
                      ->whereNull('state_en');
            });
        
        if ($roleId) {
            $query->where('id', $roleId);
            $this->info("Filtering for role ID: $roleId");
        }
        
        $roles = $query->orderBy('id', 'asc')->get();

        $bar = $this->output->createProgressBar(count($roles));
        $bar->start();

        foreach ($roles as $role) {
            if ($role->language_code == 'en') {
                $role->name_en = '';
                $role->description_en = '';
                $role->address1_en = '';
                $role->address2_en = '';
                $role->city_en = '';
                $role->state_en = '';
                $role->save();

                continue;
            }

            //try {
                if ($role->name && !$role->name_en) {
                    $role->name_en = GeminiUtils::translate($role->name, $role->language_code, 'en');
                }

                if ($role->description && !$role->description_en) {
                    $role->description_en = GeminiUtils::translate($role->description, $role->language_code, 'en');
                }

                if ($role->address1 && !$role->address1_en) {
                    $role->address1_en = GeminiUtils::translate($role->address1, $role->language_code, 'en');
                }

                if ($role->address2 && !$role->address2_en) {
                    $role->address2_en = GeminiUtils::translate($role->address2, $role->language_code, 'en');
                }

                if ($role->city && !$role->city_en) {
                    $role->city_en = GeminiUtils::translate($role->city, $role->language_code, 'en');
                }

                if ($role->state && !$role->state_en) {
                    $role->state_en = GeminiUtils::translate($role->state, $role->language_code, 'en');
                }

                $role->save();
                $bar->advance();

                sleep(rand(12, 18));
            //} catch (\Exception $e) {
            //    $this->error("\nError translating role {$role->id}: " . $e->getMessage());
            //}
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }

    public function translateEvents($eventId = null)
    {
        $this->info('Starting translation of events...');

        // Get all events that don't have English translations
        $query = Event::with('roles')
        ->where(function($query) {
            $query->whereNotNull('name')
                ->whereNull('name_en');
        })
        ->orWhere(function($query) {
            $query->whereNotNull('description')
                ->whereNull('description_en');
        });
        
        if ($eventId) {
            $query->where('id', $eventId);
            $this->info("Filtering for event ID: $eventId");
        }
        
        $events = $query->orderBy('id', 'asc')->get();

        $bar = $this->output->createProgressBar(count($events));
        $bar->start();

        foreach ($events as $event) {
            $this->info("\nTranslating event {$event->id}...");
            if ($event->getLanguageCode() == 'en') {
                $event->name_en = '';
                $event->description_en = '';
                $event->save();

                $this->info("Skipping event {$event->id} as it is already in English");
                continue;
            }

            //try {
                if ($event->name && !$event->name_en) {
                    $event->name_en = GeminiUtils::translate($event->name, $event->getLanguageCode(), 'en');
                    $this->info("Translated event {$event->id} name to {$event->name_en}");
                }

                if ($event->description && !$event->description_en) {
                    $event->description_en = GeminiUtils::translate($event->description, $event->getLanguageCode(), 'en');
                    $this->info("Translated event {$event->id} description to {$event->description_en}");
                }

                $event->save();
                $bar->advance();

                sleep(rand(12, 18));
            //} catch (\Exception $e) {
            //    $this->error("\nError translating event {$event->id}: " . $e->getMessage());
            //}
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }

    public function translateCuratorEvents($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of curator events...');

        $query = EventRole::with('role', 'event')
                    ->whereHas('role', function($query) {
                        $query->where('type', 'curator');
                    })
                    ->where(function($query) {
                        $query->whereNull('name_translated')
                              ->orWhereNull('description_translated');
                    });
        
        if ($eventId) {
            $query->whereHas('event', function($query) use ($eventId) {
                $query->where('id', $eventId);
            });
            $this->info("Filtering for event ID: $eventId");
        }
        
        if ($roleId) {
            $query->where('role_id', $roleId);
            $this->info("Filtering for role ID: $roleId");
        }
        
        $eventRoles = $query->orderBy('id', 'asc')->get();

        $bar = $this->output->createProgressBar(count($eventRoles));
        $bar->start();  

        foreach ($eventRoles as $eventRole) {        
            if ($eventRole->event->getLanguageCode() == $eventRole->role->language_code) {
                $eventRole->name_translated = '';
                $eventRole->description_translated = '';
                $eventRole->save();

                continue;
            } 

            //try {
                if ($eventRole->event->name && !$eventRole->name_translated) {
                    $eventRole->name_translated = GeminiUtils::translate($eventRole->event->name, $eventRole->event->getLanguageCode(), $eventRole->role->language_code) ?? '';
                }

                if ($eventRole->event->description && !$eventRole->description_translated) {
                    $eventRole->description_translated = GeminiUtils::translate($eventRole->event->description, $eventRole->event->getLanguageCode(), $eventRole->role->language_code) ?? '';
                }

                $eventRole->save();
                $bar->advance();

                sleep(rand(12, 18));
            //} catch (\Exception $e) {
            //    $this->error("\nError translating event role {$eventRole->id}: " . $e->getMessage());
            //}
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }   
}
