<?php

use Flutterwave\Bin;

class BinTest extends PHPUnit_Framework_TestCase {
  private $b;

  public function setUp() {
    $this->b = Bin::check("504334");
  }

  public function testResponseIsOk() {
    $this->assertTrue($this->b->isSuccessfulResponse());
  }

  public function testStatusCodeIsOk() {
    $this->assertEquals(200, $this->b->getStatusCode());
  }
}
