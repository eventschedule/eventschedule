<?php

namespace Tests\Feature;

use App\Services\TranslationOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\ResetsTranslationOverrides;
use Tests\TestCase;

/**
 * translations:publish is now on a schedule so the worker container has the operator's override
 * files. That makes its destructive half unattended, which it was never designed to be.
 *
 * publishAll() adopts hand-made files before pruning, and the comment there says adopting first
 * means pruning "can never delete overrides that only exist on disk". That holds only while
 * adoption SUCCEEDS: adoptFileOverrides() catches a Throwable and returns 0, so a file with a
 * single syntax error creates no rows, is absent from the active set, and gets deleted. A human
 * running the command can read the output and restore from their editor; cron cannot, and would
 * destroy the operator's customizations within a day.
 *
 * That would also undo SafeTranslationLoader (issue #117), which exists precisely so a broken
 * override degrades to English and stays fixable.
 */
class ScheduledTranslationPublishTest extends TestCase
{
    use RefreshDatabase;
    use ResetsTranslationOverrides;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = config('app.lang_overrides_path').'/es';
        File::ensureDirectoryExists($this->path);
    }

    public function test_an_unparseable_override_file_survives_an_unattended_publish(): void
    {
        $file = $this->path.'/messages.php';
        File::put($file, "<?php\n\nreturn [\n    'greeting' => 'Hola'   // missing comma and bracket\n");

        app(TranslationOverrideService::class)->publishAll(prune: false);

        $this->assertFileExists($file,
            'a scheduled publish deleted a hand-made override file that merely failed to parse');
    }

    /** The hand-run default keeps pruning, so an operator can still clean up removed overrides. */
    public function test_the_hand_run_default_still_prunes(): void
    {
        $file = $this->path.'/messages.php';
        File::put($file, "<?php\n\nreturn [\n    'greeting' => 'Hola'   // broken\n");

        app(TranslationOverrideService::class)->publishAll();

        $this->assertFileDoesNotExist($file);
    }

    public function test_the_scheduled_rails_both_pass_no_prune(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $rail) {
            $body = file_get_contents(base_path($rail));

            $this->assertStringContainsString("'translations:publish', ['--no-prune' => true]", $body,
                "{$rail} must not run an unattended prune");
        }
    }
}
