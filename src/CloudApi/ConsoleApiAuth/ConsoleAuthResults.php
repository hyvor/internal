<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\Dto\Me;
use Hyvor\Internal\CloudApi\JwtSource\JwtSource;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class ConsoleAuthResults
{

    public function __construct(
        private AccessType $accessType,
        private ?int $organizationId,
        private ?object $resource,

        // for session
        private ?Me $user = null,

        // for cloud token
        private ?JwtSource $jwtSource = null,

        // for product API key
        private ?object $productApiKey = null
    ) {
        if ($this->accessType === AccessType::SESSION) {
            assert($user !== null);
        }

        if ($this->accessType === AccessType::CLOUD_TOKEN) {
            assert($jwtSource !== null);
        }

        if ($this->accessType === AccessType::PRODUCT_API_KEY) {
            assert($productApiKey !== null);
        }
    }

    public function getAccessType(): AccessType
    {
        return $this->accessType;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organizationId;
    }

    public function getResource(): ?object
    {
        return $this->resource;
    }

    /**
     * Generates a standardized string representation of the source of the access.
     * for session, user:<session_id>
     * for product API key, product:<product_api_key_id>
     * for cloud API key, cloud:<jwt_source>
     *
     * We would generally save this in the logs for auditing purposes.
     */
    public function getSourceString(): string
    {
        return match ($this->accessType) {
            AccessType::SESSION => "session:{$this->user?->getSessionId()}",
            AccessType::CLOUD_TOKEN => "cloud_api:{$this->jwtSource?->getSource()}",
            AccessType::PRODUCT_API_KEY => "product_api:{$this->getProductApiKeyId($this->productApiKey ?? new \stdClass())}",
        };
    }

    private function getProductApiKeyId(object $productApiKeyEntity): int
    {
        if (method_exists($productApiKeyEntity, 'getId')) {
            return $productApiKeyEntity->getId();
        }
        return 0; // should not happen. review if an entity does not have getId() method
    }

    public function getNullableUser(): ?AuthUser
    {
        return $this->user?->getUser();
    }

}
