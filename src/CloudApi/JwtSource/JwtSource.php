<?php

namespace Hyvor\Internal\CloudApi\JwtSource;

use Hyvor\Internal\Component\Component;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * custom Jwt Source claim.
 *
 * for developer API usage, dev:<app_id>
 * for cloud API usage, cloud:<cloud_api_key_id>
 * for internal API usage, internal:<component_name>
 */
#[Exclude]
class JwtSource
{

    public function __construct(
        private JwtSourceType $type,
        private string $identifier
    ) {}

    public static function forDeveloper(string $appId): self
    {
        return new self(JwtSourceType::DEVELOPER_APP, $appId);
    }

    public static function forCloud(string $cloudApiKeyId): self
    {
        return new self(JwtSourceType::CLOUD, $cloudApiKeyId);
    }

    public static function forInternal(Component $component): self
    {
        return new self(JwtSourceType::INTERNAL, $component->value);
    }

    public static function fromString(string $src): self
    {
        if (str_starts_with($src, 'dev:')) {
            return self::forDeveloper(substr($src, 4));
        } elseif (str_starts_with($src, 'cloud:')) {
            return self::forCloud(substr($src, 6));
        } elseif (str_starts_with($src, 'internal:')) {
            $componentName = substr($src, 9);
            $component = Component::tryFrom($componentName);
            if ($component === null) {
                throw new \InvalidArgumentException('Invalid component name in JWT source: ' . $componentName);
            }
            return self::forInternal($component);
        } else {
            throw new \InvalidArgumentException('Invalid JWT source: ' . $src);
        }
    }

    public function getSource(): string
    {
        return match ($this->type) {
            JwtSourceType::DEVELOPER_APP => 'dev:' . $this->identifier,
            JwtSourceType::CLOUD => 'cloud:' . $this->identifier,
            JwtSourceType::INTERNAL => 'internal:' . $this->identifier,
        };
    }

}
