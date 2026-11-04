<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaymentReversalTest extends TestCase
{
    public function test_guest_is_redirected_from_payment_page(): void
    {
        $this->get('/payments')->assertRedirect('/login');
    }
}
