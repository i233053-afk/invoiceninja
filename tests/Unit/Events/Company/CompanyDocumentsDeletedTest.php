<?php

namespace Tests\Unit\Events\Company;

use App\Events\Company\CompanyDocumentsDeleted;
use App\Models\Company;
use PHPUnit\Framework\TestCase;

class CompanyDocumentsDeletedTest extends TestCase
{
    /** @test */
    public function it_sets_the_company_property_correctly()
    {
        // Create a simple mock for Company model
        $company = $this->createMock(Company::class);

        $event = new CompanyDocumentsDeleted($company);

        $this->assertSame($company, $event->company);
    }

    /** @test */
    public function it_returns_an_empty_array_for_broadcast_on()
    {
        $company = $this->createMock(Company::class);

        $event = new CompanyDocumentsDeleted($company);

        $this->assertSame([], $event->broadcastOn());
    }
}

