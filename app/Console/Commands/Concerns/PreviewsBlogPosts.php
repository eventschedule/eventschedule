<?php

namespace App\Console\Commands\Concerns;

use App\Models\BlogPost;

/**
 * --preview on the two blog generators: run the writer for real, show its work, save nothing.
 */
trait PreviewsBlogPosts
{
    /**
     * Print what a run of the writer produced, stage by stage, and say what would happen to it.
     *
     * @param  array<string, mixed>  $written
     */
    protected function preview(array $written): int
    {
        $this->line('Calls: '.implode(', ', array_map(fn ($call) => $call['stage'].' '.$call['seconds'].'s'.($call['ok'] ? '' : ' (nothing)'), $written['calls'])).' | '.$written['seconds'].'s in all');

        if ($written['brief']) {
            $this->newLine();
            $this->info('BRIEF');
            $this->line(json_encode($written['brief'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        if ($written['post'] === null) {
            $this->error('Nothing was written: '.$written['error'].'.');

            return 1;
        }

        foreach (['draft' => 'DRAFT', 'post' => 'AFTER THE EDIT'] as $key => $label) {
            if (! $written[$key]) {
                continue;
            }

            $this->newLine();
            $this->info($label.': '.($written[$key]['title'] ?? ''));
            $this->line((string) ($written[$key]['description'] ?? ''));
            $this->line(BlogPost::wordCountOf($written[$key]['content'] ?? '').' words');
            $this->newLine();
            $this->line((string) ($written[$key]['content'] ?? ''));
        }

        $this->newLine();
        $this->info('WHAT THE EDITOR CHANGED');
        foreach ((array) ($written['post']['fixed'] ?? []) as $fixed) {
            $this->line('- '.$fixed);
        }

        $this->newLine();
        if ($written['failures'] === []) {
            $this->info('VERDICT: would be published.');
        } else {
            $this->warn('VERDICT: would be held as a draft.');
            foreach ($written['failures'] as $failure) {
                $this->line('- '.$failure);
            }
        }

        return 0;
    }
}
