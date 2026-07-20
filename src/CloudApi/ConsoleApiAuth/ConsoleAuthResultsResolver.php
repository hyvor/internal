<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ConsoleAuthResultsResolver implements ValueResolverInterface
{

    /**
     * @return iterable<ConsoleAuthResults>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // get the argument type (e.g. BookingId)
        $argumentType = $argument->getType();

        if (!$argumentType) {
            return [];
        }

        if ($argumentType !== ConsoleAuthResults::class) {
            return [];
        }

        $value = $request->attributes->get(ConsoleApiAuthorizationListenerAbstract::ATTRIBUTE_KEY);
        if (!$value instanceof ConsoleAuthResults) {
            return [];
        }

        return [$value];
    }

}
