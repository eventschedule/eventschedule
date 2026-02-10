<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Role;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;

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
        if ($roleId && ! is_numeric($roleId)) {
            $roleId = UrlUtils::decodeId($roleId);
            $this->info("Decoded role ID: $roleId");
        }

        if ($eventId && ! is_numeric($eventId)) {
            $eventId = UrlUtils::decodeId($eventId);
            $this->info("Decoded event ID: $eventId");
        }

        // Resolve event_slug to event_id if provided
        if ($eventSlug && ! $eventId) {
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
            $this->translateEventParts(null, $roleId);
        } elseif ($eventId) {
            $this->translateEvents($eventId);
            $this->translateCuratorEvents($eventId);
            $this->translateEventParts($eventId);
        } else {
            $this->translateRoles();
            $this->translateEvents();
            $this->translateCuratorEvents();
            $this->translateEventParts();
        }
    }

    public function translateRoles($roleId = null)
    {
        $this->info('Starting translation of roles...');
        $debug = $this->option('debug');

        // Get all roles that don't have English translations
        // Exclude English roles upfront to avoid processing them
        $query = Role::where('language_code', '!=', 'en')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->whereNull('name_en');
                })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('description')
                            ->where('description', '!=', '')
                            ->whereNull('description_en');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('address1')
                            ->where('address1', '!=', '')
                            ->whereNull('address1_en');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('city')
                            ->where('city', '!=', '')
                            ->whereNull('city_en');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('state')
                            ->where('state', '!=', '')
                            ->whereNull('state_en');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('request_terms')
                            ->where('request_terms', '!=', '')
                            ->whereNull('request_terms_en');
                    })
                    ->orWhere(function ($q) {
                        // Include roles with custom fields that might need translation
                        $q->whereNotNull('event_custom_fields');
                    });
            });

        if ($roleId) {
            $query->where('id', $roleId);
            $this->info("Filtering for role ID: $roleId");
        }

        $roles = $query->orderBy('id', 'asc')->get();

        if ($debug) {
            $this->info('Found '.count($roles).' roles needing translation');
            $this->info('SQL Query: '.$query->toSql());
            $this->info('Query Bindings: '.json_encode($query->getBindings()));
        }

        $bar = $this->output->createProgressBar(count($roles));
        $bar->start();

        foreach ($roles as $role) {
            if ($role->translation_attempts >= config('usage.stuck_translation_attempts', 3)) {
                if ($debug) {
                    $this->warn("Skipping stuck role ID: {$role->id} (attempts: {$role->translation_attempts})");
                }
                $bar->advance();

                continue;
            }

            if ($debug) {
                $this->info("\nProcessing role ID: {$role->id}, Name: {$role->name}, Language: {$role->language_code}");
            }

            /*
            if ($role->language_code == 'en') {
                $role->name_en = '';
                $role->description_en = '';
                $role->address1_en = '';
                $role->address2_en = '';
                $role->city_en = '';
                $role->state_en = '';
                $role->request_terms_en = '';
                $role->save();

                if ($debug) {
                    $this->info("Skipping translation for English role ID: {$role->id}");
                }
                continue;
            }
            */

            if ($role->name && ! $role->name_en) {
                $role->name_en = GeminiUtils::translate($role->name, $role->language_code, 'en');
                if ($debug) {
                    $this->info("Translated name from {$role->language_code} to en: '{$role->name}' → '{$role->name_en}'");
                }
            }

            $glossary = [];
            if ($role->name && $role->name_en) {
                $glossary[$role->name] = $role->name_en;
            }

            if ($role->description && ! $role->description_en) {
                $role->description_en = GeminiUtils::translate($role->description, $role->language_code, 'en', $glossary);
                if ($debug) {
                    $this->info("Translated description from {$role->language_code} to en: '{$role->description}' → '{$role->description_en}'");
                }
            }

            if ($role->address1 && ! $role->address1_en) {
                $role->address1_en = GeminiUtils::translate($role->address1, $role->language_code, 'en');
                if ($debug) {
                    $this->info("Translated address1 from {$role->language_code} to en: '{$role->address1}' → '{$role->address1_en}'");
                }
            }

            if ($role->address2 && ! $role->address2_en) {
                $role->address2_en = GeminiUtils::translate($role->address2, $role->language_code, 'en');
                if ($debug) {
                    $this->info("Translated address2 from {$role->language_code} to en: '{$role->address2}' → '{$role->address2_en}'");
                }
            }

            if ($role->city && ! $role->city_en) {
                $role->city_en = GeminiUtils::translate($role->city, $role->language_code, 'en');
                if ($debug) {
                    $this->info("Translated city from {$role->language_code} to en: '{$role->city}' → '{$role->city_en}'");
                }
            }

            if ($role->state && ! $role->state_en) {
                $role->state_en = GeminiUtils::translate($role->state, $role->language_code, 'en');
                if ($debug) {
                    $this->info("Translated state from {$role->language_code} to en: '{$role->state}' → '{$role->state_en}'");
                }
            }

            if ($role->request_terms && ! $role->request_terms_en) {
                $role->request_terms_en = GeminiUtils::translate($role->request_terms, $role->language_code, 'en', $glossary);
                if ($debug) {
                    $this->info("Translated request terms from {$role->language_code} to en: '{$role->request_terms}' → '{$role->request_terms_en}'");
                }
            }

            $role->translation_attempts++;
            $role->last_translated_at = now();
            UsageTrackingService::track(UsageTrackingService::GEMINI_TRANSLATE, $role->id);

            // Translate event custom field names
            if ($role->event_custom_fields) {
                $customFields = $role->event_custom_fields;
                $fieldsNeedingTranslation = [];

                foreach ($customFields as $fieldKey => $fieldConfig) {
                    if (! empty($fieldConfig['name']) && empty($fieldConfig['name_en'])) {
                        $fieldsNeedingTranslation[$fieldKey] = $fieldConfig['name'];
                    }
                }

                if (! empty($fieldsNeedingTranslation)) {
                    if ($debug) {
                        $this->info('Translating '.count($fieldsNeedingTranslation).' custom field names');
                    }

                    try {
                        $translations = GeminiUtils::translateCustomFieldNames(
                            array_values($fieldsNeedingTranslation),
                            $role->language_code
                        );

                        foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                            if (isset($translations[$fieldName])) {
                                $customFields[$fieldKey]['name_en'] = $translations[$fieldName];
                                if ($debug) {
                                    $this->info("Translated custom field '{$fieldName}' → '{$translations[$fieldName]}'");
                                }
                            }
                        }

                        $role->event_custom_fields = $customFields;
                    } catch (\Exception $e) {
                        $this->error('Failed to translate custom field names: '.$e->getMessage());
                    }
                }
            }

            $role->save();
            if ($debug) {
                $this->info("Saved translations for role ID: {$role->id}");
            }

            $bar->advance();

            sleep(rand(12, 18));
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }

    public function translateEvents($eventId = null)
    {
        $this->info('Starting translation of events...');
        $debug = $this->option('debug');

        // Get all events that don't have English translations
        $query = Event::with(['roles', 'creatorRole'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->whereNull('name_en');
                })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('description')
                            ->where('description', '!=', '')
                            ->whereNull('description_en');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('short_description')
                            ->where('short_description', '!=', '')
                            ->whereNull('short_description_en');
                    });
            });

        if ($eventId) {
            $query->where('id', $eventId);
            $this->info("Filtering for event ID: $eventId");
        }

        $events = $query->orderBy('id', 'asc')->get();

        if ($debug) {
            $this->info('Found '.count($events).' events needing translation');
            $this->info('SQL Query: '.$query->toSql());
            $this->info('Query Bindings: '.json_encode($query->getBindings()));
        }

        $bar = $this->output->createProgressBar(count($events));
        $bar->start();

        foreach ($events as $event) {
            if ($event->translation_attempts >= config('usage.stuck_translation_attempts', 3)) {
                if ($debug) {
                    $this->warn("Skipping stuck event ID: {$event->id} (attempts: {$event->translation_attempts})");
                }
                $bar->advance();

                continue;
            }

            if ($debug) {
                $this->info("\nProcessing event ID: {$event->id}, Name: {$event->name}, Language: {$event->getLanguageCode()}");
            } else {
                $this->info("\nTranslating event {$event->id}...");
            }

            if ($event->getLanguageCode() == 'en') {
                $event->name_en = '';
                $event->description_en = '';
                $event->short_description_en = '';
                $event->save();

                if ($debug) {
                    $this->info("Skipping translation for English event ID: {$event->id}");
                }

                $bar->advance();

                continue;
            }

            $glossary = [];
            if ($event->creatorRole && $event->creatorRole->name && $event->creatorRole->name_en) {
                $glossary[$event->creatorRole->name] = $event->creatorRole->name_en;
            }

            if ($event->name && ! $event->name_en) {
                $event->name_en = GeminiUtils::translate($event->name, $event->getLanguageCode(), 'en', $glossary);
                if ($debug) {
                    $this->info("Translated name from {$event->getLanguageCode()} to en: '{$event->name}' → '{$event->name_en}'");
                }
            }

            if ($event->description && ! $event->description_en) {
                $event->description_en = GeminiUtils::translate($event->description, $event->getLanguageCode(), 'en', $glossary);
                if ($debug) {
                    $this->info("Translated description from {$event->getLanguageCode()} to en: '{$event->description}' → '{$event->description_en}'");
                }
            }

            if ($event->short_description && ! $event->short_description_en) {
                $event->short_description_en = GeminiUtils::translate($event->short_description, $event->getLanguageCode(), 'en', $glossary);
                if ($debug) {
                    $this->info("Translated short_description from {$event->getLanguageCode()} to en: '{$event->short_description}' → '{$event->short_description_en}'");
                }
            }

            $event->translation_attempts++;
            $event->last_translated_at = now();
            UsageTrackingService::track(UsageTrackingService::GEMINI_TRANSLATE, $event->creator_role_id ?? 0);

            $event->save();
            $bar->advance();

            sleep(rand(12, 18));
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }

    public function translateCuratorEvents($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of curator events...');
        $debug = $this->option('debug');

        $query = EventRole::with('role', 'event')
            ->whereHas('role', function ($query) {
                $query->where('type', 'curator');
            })
            ->where(function ($query) {
                $query->whereNull('name_translated')
                    ->orWhereNull('description_translated')
                    ->orWhereNull('short_description_translated');
            });

        if ($eventId) {
            $query->whereHas('event', function ($query) use ($eventId) {
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
            $this->info('Found '.count($eventRoles).' curator events needing translation');
            $this->info('SQL Query: '.$query->toSql());
            $this->info('Query Bindings: '.json_encode($query->getBindings()));
        }

        $bar = $this->output->createProgressBar(count($eventRoles));
        $bar->start();

        foreach ($eventRoles as $eventRole) {
            if ($eventRole->translation_attempts >= config('usage.stuck_translation_attempts', 3)) {
                if ($debug) {
                    $this->warn("Skipping stuck event role ID: {$eventRole->id} (attempts: {$eventRole->translation_attempts})");
                }
                $bar->advance();

                continue;
            }

            if ($debug) {
                $this->info("\nProcessing event role ID: {$eventRole->id}, Event ID: {$eventRole->event_id}, Role ID: {$eventRole->role_id}");
                $this->info("Event language: {$eventRole->event->getLanguageCode()}, Role language: {$eventRole->role->language_code}");
            }

            if ($eventRole->event->getLanguageCode() == $eventRole->role->language_code) {
                $eventRole->name_translated = '';
                $eventRole->description_translated = '';
                $eventRole->short_description_translated = '';
                $eventRole->save();

                if ($debug) {
                    $this->info("Skipping translation as languages match: {$eventRole->event->getLanguageCode()}");
                }

                continue;
            }

            if (! $eventRole->name_translated) {
                $fromLang = $eventRole->event->getLanguageCode();
                $toLang = $eventRole->role->language_code;
                $eventRole->name_translated = GeminiUtils::translate($eventRole->event->name, $fromLang, $toLang);
                if ($debug) {
                    $this->info("Translated event name from {$fromLang} to {$toLang}: '{$eventRole->event->name}' → '{$eventRole->name_translated}'");
                }
            }

            if (! $eventRole->description_translated) {
                $fromLang = $eventRole->event->getLanguageCode();
                $toLang = $eventRole->role->language_code;
                $eventRole->description_translated = GeminiUtils::translate($eventRole->event->description, $fromLang, $toLang);
                if ($debug) {
                    $this->info("Translated event description from {$fromLang} to {$toLang}: '{$eventRole->event->description}' → '{$eventRole->description_translated}'");
                }
            }

            if (! $eventRole->short_description_translated && $eventRole->event->short_description) {
                $fromLang = $eventRole->event->getLanguageCode();
                $toLang = $eventRole->role->language_code;
                $eventRole->short_description_translated = GeminiUtils::translate($eventRole->event->short_description, $fromLang, $toLang);
                if ($debug) {
                    $this->info("Translated event short_description from {$fromLang} to {$toLang}: '{$eventRole->event->short_description}' → '{$eventRole->short_description_translated}'");
                }
            }

            $eventRole->translation_attempts++;
            $eventRole->last_translated_at = now();
            UsageTrackingService::track(UsageTrackingService::GEMINI_TRANSLATE, $eventRole->role_id);

            $eventRole->save();
            $bar->advance();

            sleep(rand(12, 18));
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }

    public function translateEventParts($eventId = null, $roleId = null)
    {
        $this->info('Starting translation of event parts...');
        $debug = $this->option('debug');

        $query = EventPart::with(['event.roles', 'event.creatorRole'])
            ->whereHas('event.creatorRole', function ($query) {
                $query->where('language_code', '!=', 'en');
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->whereNull('name_en');
                })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('description')
                            ->where('description', '!=', '')
                            ->whereNull('description_en');
                    });
            });

        if ($eventId) {
            $query->where('event_id', $eventId);
            $this->info("Filtering for event ID: $eventId");
        }

        if ($roleId) {
            $query->whereHas('event.roles', function ($query) use ($roleId) {
                $query->where('roles.id', $roleId);
            });
            $this->info("Filtering for role ID: $roleId");
        }

        $parts = $query->orderBy('id', 'asc')->get();

        if ($debug) {
            $this->info('Found '.count($parts).' event parts needing translation');
        }

        $bar = $this->output->createProgressBar(count($parts));
        $bar->start();

        foreach ($parts as $part) {
            if ($part->translation_attempts >= config('usage.stuck_translation_attempts', 3)) {
                if ($debug) {
                    $this->warn("Skipping stuck event part ID: {$part->id} (attempts: {$part->translation_attempts})");
                }
                $bar->advance();

                continue;
            }

            $languageCode = $part->event->getLanguageCode();

            if ($debug) {
                $this->info("\nProcessing event part ID: {$part->id}, Name: {$part->name}, Event ID: {$part->event_id}, Language: {$languageCode}");
            }

            if ($languageCode == 'en') {
                $part->name_en = '';
                $part->description_en = '';
                $part->save();

                if ($debug) {
                    $this->info("Skipping translation for English event part ID: {$part->id}");
                }

                $bar->advance();

                continue;
            }

            $glossary = [];
            if ($part->event->creatorRole && $part->event->creatorRole->name && $part->event->creatorRole->name_en) {
                $glossary[$part->event->creatorRole->name] = $part->event->creatorRole->name_en;
            }

            if ($part->name && ! $part->name_en) {
                $part->name_en = GeminiUtils::translate($part->name, $languageCode, 'en', $glossary);
                if ($debug) {
                    $this->info("Translated name from {$languageCode} to en: '{$part->name}' → '{$part->name_en}'");
                }
            }

            if ($part->description && ! $part->description_en) {
                $part->description_en = GeminiUtils::translate($part->description, $languageCode, 'en', $glossary);
                if ($debug) {
                    $this->info("Translated description from {$languageCode} to en: '{$part->description}' → '{$part->description_en}'");
                }
            }

            $part->translation_attempts++;
            $part->last_translated_at = now();
            UsageTrackingService::track(UsageTrackingService::GEMINI_TRANSLATE, $part->event->creator_role_id ?? 0);

            $part->save();
            $bar->advance();

            sleep(rand(12, 18));
        }

        $bar->finish();
        $this->info("\nTranslation completed!\n");
    }
}
