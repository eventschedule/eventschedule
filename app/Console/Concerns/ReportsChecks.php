<?php

namespace App\Console\Concerns;

/**
 * Shared pass/warn/fail reporting for the two deploy commands.
 *
 * Both are run by a person watching a terminal mid-deploy, so the output has to be skimmable at
 * a glance and the exit code has to be trustworthy enough to gate on. app:check-data, the older
 * diagnostic in this directory, prints its errors and then returns nothing at all - so it exits
 * 0 with problems on screen and cannot gate anything. That is the mistake this avoids.
 *
 * Three levels, and the distinction between the last two is the useful part:
 *   pass - checked, correct.
 *   warn - checked, not correct YET, and a documented step later in the runbook fixes it.
 *          CACHE_STORE being unset before step 5 is the archetype.
 *   fail - checked, wrong, and no step in the runbook is going to fix it.
 * Only fail moves the exit code.
 */
trait ReportsChecks
{
    /** @var array<int, array{section: string, level: string, label: string, detail: ?string}> */
    private array $checks = [];

    private ?string $currentSection = null;

    protected function section(string $name): void
    {
        $this->currentSection = $name;
        $this->newLine();
        $this->line('  <options=bold>'.strtoupper($name).'</>');
    }

    protected function passed(string $label, ?string $detail = null): void
    {
        $this->record('pass', $label, $detail);
        $this->line('  <fg=green>ok</>    '.$label.$this->suffix($detail));
    }

    protected function warned(string $label, ?string $detail = null): void
    {
        $this->record('warn', $label, $detail);
        $this->line('  <fg=yellow>warn</>  '.$label.$this->suffix($detail));
    }

    protected function failed(string $label, ?string $detail = null): void
    {
        $this->record('fail', $label, $detail);
        $this->line('  <fg=red;options=bold>FAIL</>  '.$label.$this->suffix($detail));
    }

    /**
     * Not a check - context the operator needs to write down, such as the deployment id a
     * rollback would target. Never affects the exit code.
     */
    protected function note(string $label): void
    {
        $this->line('  <fg=gray>--</>    '.$label);
    }

    private function suffix(?string $detail): string
    {
        return $detail === null ? '' : '  <fg=gray>'.$detail.'</>';
    }

    private function record(string $level, string $label, ?string $detail): void
    {
        $this->checks[] = [
            'section' => $this->currentSection ?? '',
            'level' => $level,
            'label' => $label,
            'detail' => $detail,
        ];
    }

    protected function countOf(string $level): int
    {
        return count(array_filter($this->checks, fn ($c) => $c['level'] === $level));
    }

    /**
     * @return array<int, array{section: string, level: string, label: string, detail: ?string}>
     */
    protected function allChecks(): array
    {
        return $this->checks;
    }

    /**
     * Prints the tally and returns the process exit code. Failures fail; warnings do not, so a
     * pre-step run can still be green while it legitimately has work outstanding.
     */
    protected function summarise(): int
    {
        $failed = $this->countOf('fail');
        $warned = $this->countOf('warn');

        $this->newLine();
        $this->line(sprintf(
            '  <options=bold>%d passed   %d warning%s   %d failure%s</>',
            $this->countOf('pass'),
            $warned, $warned === 1 ? '' : 's',
            $failed, $failed === 1 ? '' : 's',
        ));
        $this->newLine();

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
