<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Atlassian\AtlassianExtendSocialite;
use SocialiteProviders\Facebook\FacebookExtendSocialite;
use SocialiteProviders\GitHub\GitHubExtendSocialite;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Instagram\InstagramExtendSocialite;
use SocialiteProviders\LinkedIn\LinkedInExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Meetup\MeetupExtendSocialite;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;
use SocialiteProviders\Reddit\RedditExtendSocialite;
use SocialiteProviders\Snapchat\SnapchatExtendSocialite;
use SocialiteProviders\StackExchange\StackExchangeExtendSocialite;
use SocialiteProviders\TikTok\TikTokExtendSocialite;
use SocialiteProviders\Twitch\TwitchExtendSocialite;
use SocialiteProviders\Twitter\TwitterExtendSocialite;
use SocialiteProviders\YouTube\YouTubeExtendSocialite;
use SocialiteProviders\Zoho\ZohoExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for the application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * The Socialite providers that extend Socialite when it is called.
     *
     * @var array<int, class-string>
     */
    protected array $socialiteProviders = [
        FacebookExtendSocialite::class,
        TwitterExtendSocialite::class,
        InstagramExtendSocialite::class,
        // \SocialiteProviders\InstagramBasic\InstagramBasicExtendSocialite::class,
        GitHubExtendSocialite::class,
        YouTubeExtendSocialite::class,
        GoogleExtendSocialite::class,
        LinkedInExtendSocialite::class,
        TwitchExtendSocialite::class,
        AppleExtendSocialite::class,
        MicrosoftExtendSocialite::class,
        TikTokExtendSocialite::class,
        ZohoExtendSocialite::class,
        StackExchangeExtendSocialite::class,
        GitLabExtendSocialite::class,
        RedditExtendSocialite::class,
        SnapchatExtendSocialite::class,
        MeetupExtendSocialite::class,
        // \SocialiteProviders\Bitbucket\BitbucketExtendSocialite::class,
        AtlassianExtendSocialite::class,

        // \SocialiteProviders\Trello\TrelloExtendSocialite::class,
        // \SocialiteProviders\Zoom\ZoomExtendSocialite::class,
        // \SocialiteProviders\MailChimp\MailChimpExtendSocialite::class,
        // \SocialiteProviders\Disqus\DisqusExtendSocialite::class,
        // \SocialiteProviders\Patreon\PatreonExtendSocialite::class,
        // \SocialiteProviders\PayPal\PayPalExtendSocialite::class,
        // \SocialiteProviders\Stripe\StripeExtendSocialite::class,
        // \SocialiteProviders\Venmo\VenmoExtendSocialite::class,
        // \SocialiteProviders\SoundCloud\SoundCloudExtendSocialite::class,
        // \SocialiteProviders\Spotify\SpotifyExtendSocialite::class,
        // \SocialiteProviders\ArcGIS\ArcGISExtendSocialite::class,
        // \SocialiteProviders\Fitbit\FitbitExtendSocialite::class,
        // \SocialiteProviders\Uber\UberExtendSocialite::class,
        // \SocialiteProviders\Amazon\AmazonExtendSocialite::class,
        // \SocialiteProviders\ThirtySevenSignals\ThirtySevenSignalsExtendSocialite::class,
        // \SocialiteProviders\Keycloak\KeycloakExtendSocialite::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Configuration::class, fn () => Configuration::forSymmetricSigner(
            Sha256::create(),
            InMemory::plainText(config('services.apple.private_key')),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');
        Vite::prefetch(concurrency: 3);

        $this->configureRateLimiting();
        $this->registerEventListeners();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Register the application event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Registered => SendEmailVerificationNotification is registered by the
        // framework's event service provider, enabled via withEvents() in
        // bootstrap/app.php. Registering it here would send it twice.
        foreach ($this->socialiteProviders as $provider) {
            Event::listen(SocialiteWasCalled::class, [$provider, 'handle']);
        }
    }
}
