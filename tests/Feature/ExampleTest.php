<?php
namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testTheApplicationReturnsSuccessfulResponse(): void
    {
        $this->get('/')
            ->assertOk();
    }
}
