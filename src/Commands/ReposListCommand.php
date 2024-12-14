<?php

namespace JordanPartridge\ClaudeToolsGithub\Commands;

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Sort;

class ReposListCommand extends BaseCommand
{
    protected $signature = 'repos:list
                          {--owner= : Filter by repository owner}
                          {--sort=updated : Sort repositories by (created|updated|pushed|full_name)}
                          {--direction=desc : Sort direction (asc|desc)}';

    protected $description = 'List GitHub repositories';

    public function handle()
    {
        $this->withSpinner('Fetching repositories...', function () {
            $response = $this->github()->repos()->all(
                sort: Sort::from($this->option('sort')),
                direction: Direction::from($this->option('direction'))
            );

            $repos = collect($response->json())->map(function ($repo) {
                return [
                    $repo['name'],
                    $repo['owner']['login'],
                    \Str::limit($repo['description'] ?? '', 50),
                    number_format($repo['stargazers_count']),
                    \Carbon\Carbon::parse($repo['updated_at'])->diffForHumans(),
                ];
            });

            $this->styledTable(
                ['Name', 'Owner', 'Description', 'Stars', 'Updated'],
                $repos
            );

            $this->newLine();
            $this->info('Total repositories: ' . count($repos));
        });
    }
}