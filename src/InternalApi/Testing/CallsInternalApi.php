<?php

namespace Hyvor\Internal\InternalApi\Testing;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\InternalApi;
use Hyvor\Internal\InternalApi\InternalApiMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\TestResponse;

/**
 * Use this to test the internal API implementation of the current component
 */
trait CallsInternalApi
{

    /**
     * @param array<mixed> $data
     * @param InternalApiMethod|'GET'|'POST' $method
     * @return TestResponse<JsonResponse>
     */
    public function internalApi(
        InternalApiMethod|string $method,
        string $endpoint,
        array $data = [],
        ?Component $from = null,
    ): TestResponse {
        assert(App::environment('testing'), 'This method can only be called in the testing environment');

        if (is_string($method)) {
            $method = InternalApiMethod::from($method);
        }

        $endpoint = ltrim($endpoint, '/');

        return $this->call(
            $method->value,
            '/api/internal/' . $endpoint,
            [
                'message' => InternalApi::messageFromData($data),
            ],
            [],
            [],
            [
                'HTTP_X-Internal-Api-From' => ($from ?? Component::CORE)->value,
                'HTTP_X-Internal-Api-To' => Component::current()->value,
            ]
        );
    }

}
