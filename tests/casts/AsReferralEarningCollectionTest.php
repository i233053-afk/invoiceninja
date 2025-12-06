<?php

namespace Tests\Cast;

use App\Casts\AsReferralEarningCollection;
use App\DataMapper\Referral\ReferralEarning;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class DummyModel extends Model
{
    protected $table = 'dummy';
}

class AsReferralEarningCollectionTest extends TestCase
{
    protected AsReferralEarningCollection $cast;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new AsReferralEarningCollection();
    }

    /** @test */
    public function it_returns_empty_array_when_value_is_null()
    {
        $result = $this->cast->get(new DummyModel(), 'referral', null, []);
        $this->assertSame([], $result);
    }

    /** @test */
    public function it_returns_empty_array_when_value_is_string_null()
    {
        $result = $this->cast->get(new DummyModel(), 'referral', "null", []);
        $this->assertSame([], $result);
    }

    /** @test */
    public function it_decodes_json_and_returns_array_of_referral_earning_objects()
    {
        $json = json_encode([
            ['amount' => 100, 'source' => 'order'],
            ['amount' => 50, 'source' => 'bonus'],
        ]);

        $result = $this->cast->get(new DummyModel(), 'referral', $json, []);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ReferralEarning::class, $result[0]);
        $this->assertEquals(100, $result[0]->amount);
        $this->assertEquals('order', $result[0]->source);
    }

    /** @test */
    public function it_sets_value_to_empty_json_array_when_value_is_null()
    {
        $result = $this->cast->set(new DummyModel(), 'referral', null, []);
        $this->assertEquals('[]', $result);
    }

    /** @test */
    public function it_wraps_single_referral_earning_into_array()
    {
        $earning = new ReferralEarning(['amount' => 200, 'source' => 'invite']);
        $result = $this->cast->set(new DummyModel(), 'referral', $earning, []);

        $decoded = json_decode($result, true);

        $this->assertCount(1, $decoded);
        $this->assertEquals(200, $decoded[0]['amount']);
        $this->assertEquals('invite', $decoded[0]['source']);
    }

    /** @test */
    public function it_encodes_collection_of_referral_earnings()
    {
        $earnings = [
            new ReferralEarning(['amount' => 10, 'source' => 'a']),
            new ReferralEarning(['amount' => 20, 'source' => 'b'])
        ];

        $result = $this->cast->set(new DummyModel(), 'referral', $earnings, []);
        $decoded = json_decode($result, true);

        $this->assertCount(2, $decoded);

        $this->assertEquals(10, $decoded[0]['amount']);
        $this->assertEquals('a', $decoded[0]['source']);

        $this->assertEquals(20, $decoded[1]['amount']);
        $this->assertEquals('b', $decoded[1]['source']);
    }
}

