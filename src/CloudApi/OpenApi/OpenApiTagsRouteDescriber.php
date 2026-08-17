<?php

namespace Hyvor\Internal\CloudApi\OpenApi;

use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use OpenApi\Annotations\OpenApi;
use OpenApi\Undefined;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Route;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;

#[AutoconfigureTag('nelmio_api_doc.route_describer')]
class OpenApiTagsRouteDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $path = Util::getPath($api, $route->getPath());
        $methods = $route->getMethods();

        if (count($methods) > 1) {
            throw new \LogicException(
                "Route '{$route->getPath()}' has multiple methods defined. Please define only one method per route for OpenAPI documentation."
            );
        }

        $operation = Util::getOperation($path, strtolower($methods[0]));

        // @phpstan-ignore-next-line OA has weird string defaults
        if ($operation->tags === Undefined::UNDEFINED) {
            $operation->tags = [];
        }

        $operation->tags[] = 'method:' .strtolower($reflectionMethod->getName());
        $operation->tags[] = 'group:' . $this->getGroupName($reflectionMethod);

        if (count($reflectionMethod->getAttributes(OrgEndpoint::class)) > 0) {
            $operation->tags[] = 'org-endpoint';
        }
    }

    private function getGroupName(\ReflectionMethod $reflectionMethod): string
    {
        $class = $reflectionMethod->getDeclaringClass()->getShortName();
        $withoutController = str_replace('Controller', '', $class);
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $withoutController));
    }

}
