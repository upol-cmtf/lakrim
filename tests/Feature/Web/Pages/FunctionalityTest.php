<?php
namespace Tests\Feature\Web\Pages;

use Tests\TestCase;

class FunctionalityTest extends TestCase
{
    public function testFunctionalityPageIsPublic(): void
    {
        $this->get(route('web.functionality'))
            ->assertOk()
            ->assertSee('Popis funkčnosti')
            ->assertSee('TQ01000315')
            ->assertSee('adaptivní výběr obtížnosti');
    }

    public function testFooterLinksToFunctionalityPage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('web.functionality'), false);
    }
}
