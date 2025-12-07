<?php

namespace Tests\Helper;

use Tests\TestCase;
use App\Helpers\Sanitizer;

class SanitizerTest extends TestCase
{
    /** @test */
    /** @test */
/** @test */
public function it_removes_blank_values()
{
    $input = [
        'a' => 'value',
        'b' => '',
        'c' => null,
        'd' => 0,            // removed
        'e' => false,        // removed
        'f' => '0',          // removed because '0' is falsey
    ];

    $result = Sanitizer::removeBlanks($input);

    $this->assertArrayHasKey('a', $result);

    $this->assertArrayNotHasKey('b', $result);
    $this->assertArrayNotHasKey('c', $result);
    $this->assertArrayNotHasKey('d', $result);
    $this->assertArrayNotHasKey('e', $result);
    $this->assertArrayNotHasKey('f', $result);
}



    /** @test */
    public function it_recursively_removes_blanks_in_nested_arrays()
    {
        $input = [
            'outer' => [
                'x' => '',
                'y' => 'keep',
                'z' => [
                    'n1' => null,
                    'n2' => 'value',
                ],
            ],
        ];

        $result = Sanitizer::removeBlanks($input);

        $this->assertArrayHasKey('outer', $result);
        $this->assertArrayHasKey('y', $result['outer']);
        $this->assertArrayHasKey('z', $result['outer']);

        $this->assertArrayNotHasKey('x', $result['outer']);
        $this->assertArrayNotHasKey('n1', $result['outer']['z']);
        $this->assertArrayHasKey('n2', $result['outer']['z']);
    }

    /** @test */
    public function it_handles_empty_array()
    {
        $result = Sanitizer::removeBlanks([]);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_removes_all_items_when_all_are_blank()
    {
        $input = [
            '' => '',
            'a' => null,
            'b' => false,
        ];

        $result = Sanitizer::removeBlanks($input);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_preserves_keys_after_filtering()
    {
        $input = [
            'k1' => 'abc',
            'k2' => '',
            'k3' => 'xyz'
        ];

        $result = Sanitizer::removeBlanks($input);

        $this->assertSame(['k1', 'k3'], array_keys($result));
    }

    /** @test */
    public function it_handles_deeply_nested_arrays()
    {
        $input = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'a' => '',
                        'b' => 'value',
                        'c' => [
                            'd' => null,
                            'e' => 'keep',
                        ]
                    ]
                ]
            ]
        ];

        $result = Sanitizer::removeBlanks($input);

        $this->assertArrayHasKey('b', $result['level1']['level2']['level3']);
        $this->assertArrayHasKey('e', $result['level1']['level2']['level3']['c']);

        $this->assertArrayNotHasKey('a', $result['level1']['level2']['level3']);
        $this->assertArrayNotHasKey('d', $result['level1']['level2']['level3']['c']);
    }
}

