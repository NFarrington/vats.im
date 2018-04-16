<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SupportTest extends TestCase
{
    /** @test */
    function support_page_loads_successfully()
    {
        $this->get(route('platform.support'))
            ->assertStatus(200);
    }
}
