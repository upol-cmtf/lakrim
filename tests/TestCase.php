<?php
namespace Tests;

use Database\Seeders\DifficultySeeder;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->app->make(Translator::class);
    }
}
