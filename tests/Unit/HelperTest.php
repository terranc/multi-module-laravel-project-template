<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelperTest extends TestCase
{
    /**
     * A basic unit test example.
     * @return void
     */
    public function testFormat()
    {
        $this->assertEquals("a:b:c:d", fmt("a:{b}:c:{d}", 'b', 'd'));
        $this->assertEquals("a:b:c:{", fmt("a:{b}:c:{", 'b', 'd'));
        $this->assertEquals("a:{{b:c:{", fmt("a:{{{b}:c:{", 'b', 'd'));
    }
}
