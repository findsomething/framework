<?php

class PackerTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $packer;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->packer = new \FSth\Framework\Server\Pack\Packer();

         $this->packer->setPackerHandler(new \FSth\Framework\Server\Pack\Handler());
    }

    public function testEncode()
    {
        $data = $this->packer->encode([
           'hello' => 'world'
        ]);
        
        $decodeData = $this->packer->decode($data);

        $this->assertEquals('world', $decodeData['data']['hello']);
    }
} 