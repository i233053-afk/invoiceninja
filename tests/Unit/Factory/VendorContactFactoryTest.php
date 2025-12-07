<?php

namespace Tests\Unit\Factory;

use App\Factory\VendorContactFactory;
use App\Models\VendorContact;
use Illuminate\Support\Str;
use Tests\TestCase;

class VendorContactFactoryTest extends TestCase
{
    public function test_create_initializes_vendor_contact_properly()
    {
        $companyId = 1;
        $userId = 123;

        // Call the factory
        $vendorContact = VendorContactFactory::create($companyId, $userId);

        // Assertions
        $this->assertInstanceOf(VendorContact::class, $vendorContact);
        $this->assertSame('', $vendorContact->first_name);
        $this->assertSame($userId, $vendorContact->user_id);
        $this->assertSame($companyId, $vendorContact->company_id);
        $this->assertSame(0, $vendorContact->id);
        $this->assertTrue(is_string($vendorContact->contact_key));
        $this->assertSame(32, strlen($vendorContact->contact_key)); // check 32 characters
    }
}

