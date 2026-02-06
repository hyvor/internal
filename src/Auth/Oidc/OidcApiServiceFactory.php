<?php

namespace Hyvor\Internal\Auth\Oidc;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OidcApiServiceFactory
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {}

    public function create(OidcConfig $config): OidcApiService
    {
        return new OidcApiService(
            $config,
            $this->httpClient,
            $this->cache,
            $this->logger,
            $this->denormalizer,
            $this->validator
        );
    }

}
