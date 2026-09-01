<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per scheduled task, upserted - not an append-only run log.
     *
     * The scheduler fires ~38 tasks as often as every minute, so a row per run would grow by tens
     * of thousands a day and need its own prune command. Nothing on /admin/queue asks "what did
     * this task do last Tuesday"; it asks "is anything wrong right now". One row per name answers
     * that and stays at 38 rows forever.
     *
     * The DISPLAY list comes from Schedule::events(), not from this table, so a task removed from
     * routes/console.php simply stops rendering and a task that has never run still appears. That
     * follows AdminAlertService::row()'s "no route, no row" idiom rather than adding a prune job.
     * For the same reason there is no `expression` column: the live schedule always has the
     * current cadence, whereas a stored one would be refreshed only when the task next runs - i.e.
     * stale exactly when the task has stopped, which is the case the page exists for.
     */
    public function up(): void
    {
        Schema::create('scheduled_task_runs', function (Blueprint $table) {
            $table->id();

            // Event::$description, i.e. the ->name() every entry now carries.
            $table->string('name', 191)->unique();

            $table->timestamp('last_started_at')->nullable();

            // Written by BOTH the finished and failed listeners. A failure is a finish; if only
            // the success path advanced it, every failed task would also trip the
            // "started but never finished" check and show two red states for one event.
            $table->timestamp('last_finished_at')->nullable();

            // Overlap skips get their own column, and also set last_status - except over a
            // recorded failure, which they leave alone (ScheduledTaskRecorder::recordSkip()).
            // withoutOverlapping() blocking a task is normal under load - process-queue runs a
            // 120s queue:work every minute - so a skip is evidence the scheduler is ALIVE, not
            // that the task failed. It counts toward liveness when deciding whether a task is
            // overdue.
            $table->timestamp('last_skipped_at')->nullable();

            // 'succeeded' | 'failed' | 'skipped'. Never an enum: adding a state should be a code
            // change, not a migration.
            $table->string('last_status', 16)->nullable();

            // Seconds, not milliseconds: ScheduleRunCommand hands the listener
            // round(microtime(true) - $start, 2), so anything faster than 10ms is already 0.00.
            // Naming it for what it actually holds avoids a column that silently reads zero.
            $table->decimal('last_runtime_seconds', 8, 2)->nullable();

            // text, not a varchar: exception messages are multi-line and unbounded, and the
            // clamp happens in PHP via TextUtils. A varchar here would risk a 1406 on a strict
            // connection for the sake of saving nothing.
            $table->text('last_error')->nullable();

            $table->unsignedInteger('consecutive_failures')->default(0);

            // Which rail ran it, and on which container. On App Platform the hostname is the
            // instance id, so this answers "is the worker the thing running my schedule".
            $table->string('last_via', 20)->nullable();
            $table->string('last_host', 64)->nullable();

            // created_at is the observation-window start for this task: it is set on first insert
            // and never touched again. Without it, "never run" and "overdue" are indistinguishable
            // on a fresh deploy - a daily task with no row yet is not late, it just has not come
            // round. Nothing sorts or filters on these, so no index.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_runs');
    }
};
