<?php

namespace Hyvor\Internal\Sudo;

use Hyvor\Internal\Bundle\Entity\SudoUser;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SudoUser>
 */
final class SudoUserFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return SudoUser::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'user_id' => rand(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

}
