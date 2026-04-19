<?php
namespace Tests\Feature\Web;

use Tests\TestCase;

class SavedGameTest extends TestCase
{
    private const ROUTE_NAME = 'web.saved-game';
    private const COOKIE_NAME = 'lakrim-saved-game';

    public function testReturnsFalseWhenCookieIsMissing(): void
    {
        $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'has_saved_game' => false,
                ],
            ]);
    }

    public function testReturnsTrueWhenCookieIsPresent(): void
    {
        $this->withUnencryptedCookie(self::COOKIE_NAME, 'some-saved-state')
            ->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'has_saved_game' => true,
                ],
            ]);
    }

    public function testReturnsFalseWhenCookieIsEmpty(): void
    {
        $this->withUnencryptedCookie(self::COOKIE_NAME, '')
            ->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'has_saved_game' => false,
                ],
            ]);
    }
}
