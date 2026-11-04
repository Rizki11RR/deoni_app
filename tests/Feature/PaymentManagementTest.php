<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    public function test_guest_is_redirected_from_payments(): void
    {
        $this->get('/payments')->assertRedirect('/login');
    }
}
