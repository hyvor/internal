<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Resource;

class OrganizationResource implements \JsonSerializable
{

    public function __construct(
        public int $id,
        public \DateTimeImmutable $createdAt,
        public string $name,
        public int $createdUserId,
        public int $accessUserCount,
        public string $consoleAccessLink,
        /**
         * @var int[]
         * Up to 12 data points of usage (grouped monthly)
         * Set usageName in the response to set what this is
         */
        public array $usage,
    )
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt->getTimestamp(),
            'name' => $this->name,
            'created_user_id' => $this->createdUserId,
            'access_user_count' => $this->accessUserCount,
            'console_access_link' => $this->consoleAccessLink,
            'usage' => $this->usage
        ];
    }
}
