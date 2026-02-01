<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Property\LicensePropertyType;
use Hyvor\Internal\Billing\License\TalkLicense;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(License::class)]
#[CoversClass(BlogsLicense::class)]
#[CoversClass(CoreLicense::class)]
#[CoversClass(TalkLicense::class)]
#[CoversClass(PostLicense::class)]
class LicenseTest extends TestCase
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
     */
    #[DataProvider('getAllLicenseClasses')]
    public function test_license_properties(string $licenseClass): void
    {
        $declaredProperties = $licenseClass::properties();

        $classReflection = new \ReflectionClass($licenseClass);
        $propertyReflections = $classReflection->getProperties();

        $this->assertTrue($classReflection->isFinal());

        foreach ($propertyReflections as $propertyReflection) {

            $key = $propertyReflection->getName();
            $type = $propertyReflection->getType();

            assert($type instanceof \ReflectionNamedType);
            $typeName = $type->getName();

            // check that the property is either int or bool
            $this->assertTrue($typeName === 'int' || $typeName === 'bool');
            $this->assertFalse($type->allowsNull());

            $propertyType = LicensePropertyType::from($typeName);

            // check that this property is declared
            $declaredProperty = array_find($declaredProperties, fn($p) => $p->key === $key);
            $this->assertNotNull($declaredProperty, 'all properties must be declared: ' . $key . ' missing in ' . $licenseClass);

            $this->assertSame($propertyType, $declaredProperty->type);
            $this->assertNotEmpty($declaredProperty->name);
            $this->assertNotEmpty($declaredProperty->description);

        }

        $this->assertSame(
            count($declaredProperties),
            count($propertyReflections),
            'all declared properties must exist in class: ' . $licenseClass
        );
    }


    public function test_build_from_array(): void
    {

        $license = BlogsLicense::fromArray(
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
        $this->assertSame(false, $license->analyses);
    }


}
