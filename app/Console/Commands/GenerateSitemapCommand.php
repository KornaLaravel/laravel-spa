<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Throwable;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Named "generate-sitemap" to match the daily schedule entry in
     * routes/console.php, which predates this command.
     *
     * @var string
     */
    protected $signature = 'generate-sitemap {--url= : The URL to crawl, defaults to config("app.url")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl the application and write public/sitemap.xml';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $url = $this->option('url') ?: config('app.url');

        if (! is_string($url) || $url === '') {
            $this->error('No URL to crawl. Set APP_URL or pass --url.');

            return self::FAILURE;
        }

        $path = public_path('sitemap.xml');

        $this->info("Crawling {$url} ...");

        try {
            SitemapGenerator::create($url)->writeToFile($path);
        } catch (Throwable $e) {
            $this->error("Sitemap generation failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Sitemap written to {$path}");

        return self::SUCCESS;
    }
}
