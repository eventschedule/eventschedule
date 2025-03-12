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
    protected $signature = 'app:translate {--role_id= : Translate only a specific role by ID} {--event_id= : Translate only a specific event by ID} {--event_slug= : Translate only a specific event by slug} {--debug : Enable debug mode with verbose logging}';

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
        $debug = $this->option('debug');

        if ($debug) {
            $this->info('Debug mode enabled - verbose logging will be shown');
        }

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
        $debug = $this->option('debug');

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

        if ($debug) {
            $this->info("Found " . count($roles) . " roles needing translation");
            $this->info("SQL Query: " . $query->toSql());
            $this->info("Query Bindings: " . json_encode($query->getBindings()));
        }

        $bar = $this->output->createProgressBar(count($roles));
        $bar->start();

        foreach ($roles as $role) {
            if ($debug) {
                $this->info("\nProcessing role ID: {$role->id}, Name: {$role->name}, Language: {$role->language_code}");
            }
            
            if ($role->language_code == 'en') {
                $role->name_en = '';
                $role->description_en = '';
                $role->address1_en = '';
                $role->address2_en = '';
                $role->city_en = '';
                $role->state_en = '';
                $role->save();

                if ($debug) {
                    $this->info("Skipping translation for English role ID: {$role->id}");
                }
                continue;
            }

            //try {
                if ($role->name && !$role->name_en) {
                    $role->name_en = GeminiUtils::translate($role->name, $role->language_code, 'en');
                    if ($debug) {
                        $this->info("Translated name: '{$role->name}' → '{$role->name_en}'");
                    }
                }

                if ($role->description && !$role->description_en) {
                    $role->description_en = GeminiUtils::translate($role->description, $role->language_code, 'en');
                    if ($debug) {
                        $this->info("Translated description from {$role->language_code} to English");
                    }
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
                if ($debug) {
                    $this->info("Saved translations for role ID: {$role->id}");
                }
                
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
        $debug = $this->option('debug');

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

        if ($debug) {
            $this->info("Found " . count($events) . " events needing translation");
            $this->info("SQL Query: " . $query->toSql());
            $this->info("Query Bindings: " . json_encode($query->getBindings()));
        }

        $bar = $this->output->createProgressBar(count($events));
        $bar->start();

        foreach ($events as $event) {
            if ($debug) {
                $this->info("\nProcessing event ID: {$event->id}, Name: {$event->name}, Language: {$event->getLanguageCode()}");
            } else {
                $this->info("\nTranslating event {$event->id}...");
            }
            
            if ($event->getLanguageCode() == 'en') {
                $event->name_en = '';
                $event->description_en = '';
                $event->save();

                if ($debug) {
                    $this->info("Skipping translation for English event ID: {$event->id}");
                } else {
                    $this->info("Skipping event {$event->id} as it is already in English");
                }
                continue;
            }

            //try {
                if ($event->name && !$event->name_en) {
                    $event->name_en = GeminiUtils::translate($event->name, $event->getLanguageCode(), 'en');
                    if ($debug) {
                        $this->info("Translated name: '{$event->name}' → '{$event->name_en}'");
                    } else {
                        $this->info("Translated event {$event->id} name to {$event->name_en}");
                    }
                }

                if ($event->description && !$event->description_en) {
                    $event->description_en = GeminiUtils::translate($event->description, $event->getLanguageCode(), 'en');
                    if ($debug) {
                        $this->info("Translated description from {$event->language_code} to English");
                    }
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
        $debug = $this->option('debug');

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

        if ($debug) {
            $this->info("Found " . count($eventRoles) . " curator events needing translation");
            $this->info("SQL Query: " . $query->toSql());
            $this->info("Query Bindings: " . json_encode($query->getBindings()));
            
            // Dump the first few records to inspect
            if (count($eventRoles) > 0) {
                $this->info("Sample records that matched the query:");
                foreach ($eventRoles->take(3) as $index => $er) {
                    $this->info("Record #{$index}:");
                    $this->info("  ID: {$er->id}");
                    $this->info("  Event ID: {$er->event_id}");
                    $this->info("  Role ID: {$er->role_id}");
                    $this->info("  name_translated: " . ($er->name_translated === null ? 'NULL' : "'{$er->name_translated}'"));
                    $this->info("  description_translated: " . ($er->description_translated === null ? 'NULL' : "'{$er->description_translated}'"));
                }
            }
        }

        $bar = $this->output->createProgressBar(count($eventRoles));
        $bar->start();  

        foreach ($eventRoles as $eventRole) {        
            if ($debug) {
                $this->info("\nProcessing event role ID: {$eventRole->id}, Event ID: {$eventRole->event_id}, Role ID: {$eventRole->role_id}");
                $this->info("Event language: {$eventRole->event->getLanguageCode()}, Role language: {$eventRole->role->language_code}");
            }
            
            if ($eventRole->event->getLanguageCode() == $eventRole->role->language_code) {
                $eventRole->name_translated = '';
                $eventRole->description_translated = '';
                $eventRole->save();

                if ($debug) {
                    $this->info("Skipping translation as languages match: {$eventRole->event->getLanguageCode()}");
                }
                continue;
            } 

            //try {
                if ($eventRole->event->name && !$eventRole->name_translated) {
                    $eventRole->name_translated = GeminiUtils::translate($eventRole->event->name, $eventRole->event->getLanguageCode(), $eventRole->role->language_code) ?? '';
                    if ($debug) {
                        $this->info("Translated event name from {$eventRole->event->getLanguageCode()} to {$eventRole->role->language_code}");
                        $this->info("Original: '{$eventRole->event->name}' → Translated: '{$eventRole->name_translated}'");
                    }
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
