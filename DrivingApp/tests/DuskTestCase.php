<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        foreach (['admin', 'instructor', 'student', 'guest', 'branch'] as $roleFolder) {
            $path = base_path('tests/screenshots/' . $roleFolder);

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function captureRoleScreenshot(Browser $browser, string $role, string $scenario, string $stepName): void
    {
        $roleFolder = match (strtolower(trim($role))) {
            'admin' => 'admin',
            'instructor' => 'instructor',
            'student' => 'student',
            'guest' => 'guest',
            'branch', 'branch_secretary', 'branch secretary' => 'branch',
            default => 'guest',
        };

        $scenarioSlug = Str::slug($scenario ?: 'scenario');
        $stepSlug = Str::slug($stepName ?: 'step');

        $browser->screenshot("../../screenshots/{$roleFolder}/{$scenarioSlug}/{$stepSlug}");
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        // Use Microsoft Edge options
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        // Create Edge-specific capabilities
        $capabilities = DesiredCapabilities::microsoftEdge();
        $capabilities->setCapability('ms:edgeOptions', [
            'binary' => 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
            'args' => $options->toArray()['args'] ?? [],
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            $capabilities
        );
    }
}
