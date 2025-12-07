<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\QuoteSync;
use App\Casts\QuoteSyncCast;
use PHPUnit\Framework\TestCase;

class QuoteSyncTest extends TestCase
{
    /** @test */
    public function it_sets_qb_id_from_constructor()
    {
        $qbId = 'QB-123';
        $quoteSync = new QuoteSync(['qb_id' => $qbId]);

        $this->assertEquals($qbId, $quoteSync->qb_id);
    }

    /** @test */
    public function it_defaults_qb_id_to_empty_string()
    {
        $quoteSync = new QuoteSync([]);

        $this->assertEquals('', $quoteSync->qb_id);
    }

    /** @test */
    public function cast_using_returns_correct_cast_class()
    {
        $castClass = QuoteSync::castUsing([]);
        $this->assertEquals(QuoteSyncCast::class, $castClass);
    }

    /** @test */
    public function from_array_creates_instance_correctly()
    {
        $data = ['qb_id' => 'QB-456'];
        $quoteSync = QuoteSync::fromArray($data);

        $this->assertInstanceOf(QuoteSync::class, $quoteSync);
        $this->assertEquals('QB-456', $quoteSync->qb_id);
    }

    /** @test */
    public function from_array_with_empty_data_defaults_qb_id()
    {
        $quoteSync = QuoteSync::fromArray([]);

        $this->assertInstanceOf(QuoteSync::class, $quoteSync);
        $this->assertEquals('', $quoteSync->qb_id);
    }
}

