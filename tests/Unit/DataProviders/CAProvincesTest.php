<?php

namespace Tests\Unit\DataProviders;

use PHPUnit\Framework\TestCase;
use App\DataProviders\CAProvinces;

class CAProvincesTest extends TestCase
{
    /** @test */
    public function it_returns_all_provinces()
    {
        $provinces = CAProvinces::get();
        $this->assertIsArray($provinces);
        $this->assertArrayHasKey('ON', $provinces);
        $this->assertSame('Ontario', $provinces['ON']);
    }

    /** @test */
    public function it_returns_province_name_for_abbreviation()
    {
        $name = CAProvinces::getName('BC');
        $this->assertSame('British Columbia', $name);

        $name = CAProvinces::getName('PE');
        $this->assertSame('Prince Edward Island', $name);
    }

    /** @test */
    public function it_returns_abbreviation_for_province_name()
    {
        $abbrev = CAProvinces::getAbbreviation('Ontario');
        $this->assertSame('ON', $abbrev);

        $abbrev = CAProvinces::getAbbreviation('Nova Scotia');
        $this->assertSame('NS', $abbrev);
    }

    /** @test */
    public function it_is_case_insensitive_for_get_abbreviation()
    {
        $abbrev = CAProvinces::getAbbreviation('ontario');
        $this->assertSame('ON', $abbrev);

        $abbrev = CAProvinces::getAbbreviation('nEW brunswick');
        $this->assertSame('NB', $abbrev);
    }

    /** @test */
    public function getName_returns_null_for_invalid_abbreviation()
    {
        $this->assertNull(CAProvinces::getName('XX') ?? null);
    }

    /** @test */
    public function getAbbreviation_returns_false_for_invalid_name()
    {
        $this->assertFalse(CAProvinces::getAbbreviation('Atlantis'));
    }
}

