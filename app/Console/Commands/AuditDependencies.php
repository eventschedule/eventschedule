<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AuditDependencies extends Command
{
    protected $signature = 'app:audit';

    protected $description = 'Audit PHP and Node.js dependencies for known vulnerabilities';

    public function handle(): int
    {
        $hasVulnerabilities = false;

        // Composer audit
        $this->info('Checking PHP dependencies...');
        $composerOutput = null;
        $composerResult = null;
        exec('composer audit --format=json 2>/dev/null', $composerOutput, $composerResult);

        $composerJson = json_decode(implode('', $composerOutput), true);
        $composerAdvisories = $composerJson['advisories'] ?? [];
        $composerCount = 0;

        foreach ($composerAdvisories as $package => $advisories) {
            foreach ($advisories as $advisory) {
                $this->warn("  PHP: {$package} - {$advisory['title']}");
                $composerCount++;
            }
        }

        if ($composerCount === 0) {
            $this->info('  No known PHP vulnerabilities found.');
        } else {
            $this->error("  Found {$composerCount} PHP vulnerability(ies).");
            $hasVulnerabilities = true;
        }

        // NPM audit
        $this->info('Checking Node.js dependencies...');
        $npmOutput = null;
        $npmResult = null;
        exec('npm audit --json 2>/dev/null', $npmOutput, $npmResult);

        $npmJson = json_decode(implode('', $npmOutput), true);
        $npmVulnerabilities = $npmJson['vulnerabilities'] ?? [];
        $npmCount = 0;

        foreach ($npmVulnerabilities as $package => $info) {
            $severity = $info['severity'] ?? 'unknown';
            $this->warn("  Node: {$package} ({$severity})");
            $npmCount++;
        }

        if ($npmCount === 0) {
            $this->info('  No known Node.js vulnerabilities found.');
        } else {
            $this->error("  Found {$npmCount} Node.js vulnerability(ies).");
            $hasVulnerabilities = true;
        }

        // Summary
        $this->newLine();
        $total = $composerCount + $npmCount;
        if ($total === 0) {
            $this->info('All dependencies are clean.');
        } else {
            $this->error("Total: {$total} vulnerability(ies) found.");
        }

        return $hasVulnerabilities ? Command::FAILURE : Command::SUCCESS;
    }
}
