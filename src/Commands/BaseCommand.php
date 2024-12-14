<?php

namespace JordanPartridge\ClaudeToolsGithub\Commands;

use Illuminate\Console\Command;
use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;

abstract class BaseCommand extends Command
{
    /**
     * Determine if we're running in Laravel Zero
     */
    protected function isLaravelZero(): bool
    {
        return class_exists(LaravelZeroCommand::class);
    }

    /**
     * Get the Github client instance
     */
    protected function github()
    {
        return app('github');
    }

    /**
     * Create a styled table output
     */
    protected function styledTable(array $headers, array $rows)
    {
        // If we're in Laravel Zero, we can use the custom styling
        if ($this->isLaravelZero()) {
            return $this->table($headers, $rows, 'box-double');
        }

        // Otherwise use standard Laravel table
        return $this->table($headers, $rows);
    }

    /**
     * Show a spinner while executing a callback
     */
    protected function withSpinner(string $message, callable $callback)
    {
        if ($this->isLaravelZero()) {
            $this->task($message, $callback);
        } else {
            $this->info($message);
            $callback();
        }
    }
}