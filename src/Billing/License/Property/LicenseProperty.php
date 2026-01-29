<?php

namespace Hyvor\Internal\Billing\License\Property;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class LicenseProperty
{

    public string $name = '';
    public string $description = '';
    public ?string $note = '';

    public LicensePropertyNumberFormatting $numberFormatting = LicensePropertyNumberFormatting::DEFAULT;

    private function __construct(public string $key, public LicensePropertyType $type)
    {
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function note(string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function bytes(): self
    {
        assert($this->type === LicensePropertyType::INT);
        $this->numberFormatting = LicensePropertyNumberFormatting::BYTES;
        return $this;
    }

    public static function int(string $key): self
    {
        return new self($key, LicensePropertyType::INT);
    }

    public static function bool(string $key): self
    {
        return new self($key, LicensePropertyType::BOOL);
    }

    public function isInt(): bool
    {
        return $this->type === LicensePropertyType::INT;
    }

    public function isBool(): bool
    {
        return $this->type === LicensePropertyType::BOOL;
    }

}
