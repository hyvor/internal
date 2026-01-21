<?php

namespace Hyvor\Internal\Tests\Unit\Billing\License\Reflection;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\LicenseReflection\LicensePropertyType;
use Hyvor\Internal\Billing\LicenseReflection\LicenseReflection;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LicenseReflection::class)]
class LicenseReflectionTest extends SymfonyTestCase
{

    /**
     * @return class-string<License>[][]
     */
    public static function getAllLicenseClasses(): array
    {
        $classes = [];
        foreach (Component::cases() as $component) {
            $classes[] = [$component->license()];
        }
        return $classes;
    }

    /**
     * @param class-string<License> $licenseClass
     * @return void
     */
    #[DataProvider('getAllLicenseClasses')]
    public function test_gets_properties(string $licenseClass): void
    {
        $reflection = new LicenseReflection();
        $properties = $reflection->getPropertiesOf($licenseClass);

        // check one
        if ($licenseClass === BlogsLicense::class) {
            $this->assertCount(5, $properties);

            $this->assertSame('users', $properties[0]->name);
            $this->assertSame(LicensePropertyType::INT, $properties[0]->type);
            $this->assertSame(2, $properties[0]->default);

            $this->assertSame('analyses', $properties[4]->name);
            $this->assertSame(LicensePropertyType::BOOL, $properties[4]->type);
            $this->assertSame(true, $properties[4]->default);
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    public function test_build_from_array(): void
    {
        $reflection = new LicenseReflection();

        $license = $reflection->buildLicenseFromArray(
            BlogsLicense::class,
            // @phpstan-ignore-next-line
            [
                'users' => 1000,

                // safely skips the following
                'invalidkey' => 0, // invalid key
                'aiTokens' => false, // invalid type
                'analyses' => 30,
                'badtype' => 'ohno'
            ]
        );

        $this->assertInstanceOf(BlogsLicense::class, $license);
        $this->assertSame(1000, $license->users);
        $this->assertSame(true, $license->analyses);
    }

}