<?php
namespace Tests\Feature\Web\Pages;

use Tests\TestCase;

class LicenseTest extends TestCase
{
    public function testLicensePageIsPublic(): void
    {
        $this->get(route('web.license'))
            ->assertOk()
            ->assertSee('Licenční podmínky')
            ->assertSee('CC BY 4.0')
            ->assertSee('licencí MIT');
    }

    public function testFooterLinksToLicensePage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('web.license'), false);
    }
}
