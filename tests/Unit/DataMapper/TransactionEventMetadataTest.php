<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\TransactionEventMetadata;
use App\DataMapper\TaxReport\TaxReport;
use App\Casts\TransactionEventMetadataCast;
use PHPUnit\Framework\TestCase;

class TransactionEventMetadataTest extends TestCase
{

/** @test */
public function it_sets_tax_report_from_constructor()
{
    $taxReportData = []; // can pass empty or real data compatible with TaxReport
    $metadata = new TransactionEventMetadata(['tax_report' => $taxReportData]);

    $this->assertInstanceOf(TaxReport::class, $metadata->tax_report);

    // Instead of checking for ['total' => 100], check keys actually returned by TaxReport
    $array = $metadata->tax_report->toArray();
    $this->assertArrayHasKey('tax_summary', $array);
    $this->assertArrayHasKey('tax_details', $array);
    $this->assertArrayHasKey('payment_history', $array);
}

/** @test */
public function it_defaults_tax_report_when_not_provided()
{
    $metadata = new TransactionEventMetadata([]);

    $this->assertInstanceOf(TaxReport::class, $metadata->tax_report);

    $expected = [
        'tax_summary' => null,
        'tax_details' => null,
        'payment_history' => null,
    ];

    $this->assertEquals($expected, $metadata->tax_report->toArray());
}


    /** @test */
    public function cast_using_returns_correct_cast_class()
    {
        $castClass = TransactionEventMetadata::castUsing([]);
        $this->assertEquals(TransactionEventMetadataCast::class, $castClass);
    }

/** @test */
public function from_array_creates_instance_correctly()
{
    $data = [
        'tax_report' => [], // pass whatever data TaxReport expects
    ];

    $metadata = TransactionEventMetadata::fromArray($data);

    $this->assertInstanceOf(TransactionEventMetadata::class, $metadata);
    $this->assertInstanceOf(TaxReport::class, $metadata->tax_report);

    $expected = [
        'tax_summary' => null,
        'tax_details' => null,
        'payment_history' => null,
    ];

    $this->assertEquals($expected, $metadata->tax_report->toArray());
}

/** @test */
public function to_array_returns_correct_structure()
{
    $metadata = new TransactionEventMetadata([
        'tax_report' => [], // or populate valid TaxReport data if needed
    ]);

    $expected = [
        'tax_report' => [
            'tax_summary' => null,
            'tax_details' => null,
            'payment_history' => null,
        ],
    ];

    $this->assertEquals($expected, $metadata->toArray());
}

/** @test */
public function from_array_with_empty_data_defaults_tax_report()
{
    $metadata = TransactionEventMetadata::fromArray([]);

    $expected = [
        'tax_report' => [
            'tax_summary' => null,
            'tax_details' => null,
            'payment_history' => null,
        ],
    ];

    $this->assertEquals($expected, $metadata->toArray());
}

}

