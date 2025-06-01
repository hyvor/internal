<?php

namespace Hyvor\Internal\InternalApi\Testing;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Laravel\Laravel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * LARAVEL ONLY: Use CallsInternalApiSymfony for Symfony
 * Use this to test the internal API implementation of the current component
 */
trait CallsInternalApi
{

    /**
     * @param array<mixed> $data
     * @return TestResponse<JsonResponse>
     */
    public function internalApi(
        string $endpoint,
        array $data = [],
        ?Component $from = null,
    ): TestResponse {
        $endpoint = ltrim($endpoint, '/');

        assert(App::environment('testing'), 'This method can only be called in the testing environment');

        /*if (!App::has(HttpClientInterface::class)) {
            app()->bind(HttpClientInterface::class, fn() => new MockHttpClient());
        }*/

        $internalApi = app(InternalApi::class);
        $internalConfig = app(InternalConfig::class);

        return $this->call(
            'POST',
            '/api/internal/' . $endpoint,
            [
                'message' => $internalApi->messageFromData($data),
            ],
            [],
            [],
            [
                'HTTP_X-Internal-Api-From' => ($from ?? Component::CORE)->value,
                'HTTP_X-Internal-Api-To' => $internalConfig->getComponent()->value,
            ]
        );
    }

}
