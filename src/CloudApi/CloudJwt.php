<?php

namespace Hyvor\Internal\CloudApi;

use Firebase\JWT\JWT;
use Hyvor\Internal\CloudApi\Exception\JwtDecodeException;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;
use Hyvor\Internal\Component\Component;

class CloudJwt
{

    public const int DEFAULT_EXPIRATION = 3600; // 1 hour

    // issuer (ex: https://hyvor.com)
    private string $iss;

    // subject (organization ID)
    private string $sub;

    // issued at (timestamp)
    private int $iat;

    // not valid before (timestamp)
    private int $nbf;

    // expires (timestamp)
    private int $exp;

    // scope
    private ScopeBuilder $scope;

    /**
     * @param array<string, string> $data
     * @throws JwtDecodeException
     */
    public static function fromArray(array $data): self
    {
        $jwt = new self();

        try {
            $jwt->iss = $data['iss'];
            $jwt->sub = $data['sub'];
            $jwt->iat = (int) $data['iat'];
            $jwt->nbf = (int) $data['nbf'];
            $jwt->exp = (int) $data['exp'];
            $jwt->scope = ScopeBuilder::fromScopeString($data['scope']);
        } catch (\Throwable $e) {
            throw new JwtDecodeException('Invalid JWT payload: unable to parse required fields', previous: $e);
        }

        return $jwt;
    }

    public static function create(
        string $instanceUrl,
        int $orgId,
        ScopeBuilder $scopeBuilder,
        ?int $now = null
    ): self
    {
        $jwt = new self();
        $now ??= time();

        $jwt->iss = $instanceUrl;
        $jwt->sub = (string) $orgId;
        $jwt->iat = $now;
        $jwt->nbf = $now;
        $jwt->exp = $now + self::DEFAULT_EXPIRATION; // expires in 1 hour
        $jwt->scope = $scopeBuilder;

        return $jwt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'iss' => $this->iss,
            'sub' => $this->sub,
            'iat' => $this->iat,
            'nbf' => $this->nbf,
            'exp' => $this->exp,
            'scope' => $this->scope->getScopeString(),
        ];
    }

    /**
     * @return array<string>
     */
    public function getScopesFor(Component $component): array
    {
        $scopes = $this->scope->getScopes();
        return $scopes[$component->value] ?? [];
    }

    public function encode(string $privateKey, string $keyId): string
    {
        return JWT::encode($this->toArray(), $privateKey, 'RS256', $keyId);
    }

    public function getExpiresAt(): int
    {
        return $this->exp;
    }

    public function getOrganizationId(): int
    {
        return (int) $this->sub;
    }

}
