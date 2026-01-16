<?php
namespace App\Traits;

trait RedirectsUsers
{
    abstract protected function getRedirectPath(): string;

    public function redirectPath(): string
    {
        return $this->getRedirectPath();
    }
}
