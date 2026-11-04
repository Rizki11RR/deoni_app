<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderDiscountTest extends TestCase
{
    public function test_guest_is_redirected_from_order_form(): void
    {
        $this->get('/orders/create')->assertRedirect('/login');
    }
}
