<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    public function test_guest_is_redirected_from_orders(): void
    {
        $this->get('/orders')->assertRedirect('/login');
    }
}
