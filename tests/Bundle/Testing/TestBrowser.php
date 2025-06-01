<?php

namespace Hyvor\Internal\Tests\Bundle\Testing;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractBrowser<Request, Response>
 */
class TestBrowser extends AbstractBrowser
{

    public string $json;
    public int $status = 200;

    public function setJson(string $json, int $status): void
    {
        $this->json = $json;
        $this->status = $status;
    }

    protected function doRequest(object $request): Response
    {
        return new Response($this->json, $this->status, [
            'Content-Type' => 'application/json',
        ]);
    }
}