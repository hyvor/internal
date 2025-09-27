<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\CoreLicense;
use Hyvor\Internal\Billing\License\DerivedFrom;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\TalkLicense;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(License::class)]
#[CoversClass(BlogsLicense::class)]
#[CoversClass(CoreLicense::class)]
#[CoversClass(TalkLicense::class)]
#[CoversClass(PostLicense::class)]
class LicenseTest extends TestCase
{

    public function testCoreLicense(): void
    {
        $license = new CoreLicense();
        $license->setDerivedFrom(DerivedFrom::CUSTOM_RESOURCE);
        $this->assertEquals(DerivedFrom::CUSTOM_RESOURCE, $license->derivedFrom);
        // add more tests when we have features
    }

    public function testBlogsLicense(): void
    {
        $license = new BlogsLicense();

        $this->assertEquals(2, $license->users);
        $this->assertEquals(1_000_000_000, $license->storage);
    }

    public function test_talk_license(): void
    {
        $license = new TalkLicense();
        $this->assertEquals(1_000, $license->credits);
        $this->assertEquals(100_000_000, $license->storage);
        $this->assertTrue($license->sso);
        $this->assertFalse($license->noBranding);
        $this->assertTrue($license->webhooks);
    }

    public function test_post_license(): void
    {
        $license = new PostLicense();
        $this->assertEquals(0, $license->emails);
        $this->assertFalse($license->allowRemoveBranding);
    }

}
