<?php

namespace Bcismariu\Laravel\Payments\Tests;

use PHPUnit\Framework\TestCase;
use Bcismariu\Laravel\Payments\Card;

class CardTest extends TestCase
{
    public function testConstructor()
    {
        $card = new Card([
            'brand'     => 'visa',
            'number'    => '1234123412341234',
            'exp_month' => '02',
            'exp_year'  => '2017',
            'cvc_check' => '123',
            'something_else' => 'some value'
        ]);
        $this->assertEquals('visa', $card->brand);
        $this->assertEquals('1234123412341234', $card->number);
        $this->assertEquals('02', $card->exp_month);
        $this->assertEquals('2017', $card->exp_year);
        $this->assertEquals('123', $card->cvc_check);
        $this->assertNull($card->something_else);
        $this->assertNull($card->unset_value);
    }
}
