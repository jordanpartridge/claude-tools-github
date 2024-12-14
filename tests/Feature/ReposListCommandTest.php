<?php

namespace JordanPartridge\ClaudeToolsGithub\Tests\Feature;

use GuzzleHttp\Psr7\Response as PsrResponse;
use JordanPartridge\GithubClient\Github;
use Orchestra\Testbench\TestCase;
use JordanPartridge\ClaudeToolsGithub\CommandServiceProvider;
use JordanPartridge\GithubClient\GithubClientServiceProvider;
use Saloon\Http\Response;
use Mockery;

class ReposListCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CommandServiceProvider::class,
            GithubClientServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a PSR-7 response with our test data
        $psrResponse = new PsrResponse(
            200, 
            ['Content-Type' => 'application/json'],
            json_encode([
                [
                    'name' => 'github-client',
                    'owner' => [
                        'login' => 'jordanpartridge',
                    ],
                    'html_url' => 'https://github.com/jordanpartridge/github-client',
                    'git_url' => 'git://github.com/jordanpartridge/github-client.git',
                    'description' => 'An elegant GitHub client',
                    'created_at' => '2024-11-14T00:00:00Z',
                    'updated_at' => now()->subDay()->toIso8601String(),
                    'stargazers_count' => 42,
                    'forks_count' => 7,
                    'private' => false,
                ])
            ])
        );

        // Create a Saloon response
        $response = new Response($psrResponse);

        // Create a mock connector that returns our response
        $connector = Mockery::mock(\JordanPartridge\GithubClient\Contracts\GithubConnectorInterface::class);
        $connector->shouldReceive('send')->andReturn($response);

        // Create Github instance with mock connector
        $github = new Github($connector);
        
        // Bind the Github instance to the container
        $this->app->instance(Github::class, $github);
    }

    /** @test */
    public function it_can_list_repositories(): void
    {
        $this->artisan('repos:list')
            ->expectsOutput('Fetching repositories...')
            ->expectsTable(
                ['Name', 'Owner', 'Description', 'Stars', 'Updated'],
                [
                    ['github-client', 'jordanpartridge', 'An elegant GitHub client', '42', '1 day ago'],
                ]
            )
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}