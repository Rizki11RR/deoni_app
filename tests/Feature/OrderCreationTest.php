<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    public function test_guest_is_redirected_from_order_creation(): void
    {
        $this->get('/orders/create')->assertRedirect('/login');
    }
}
