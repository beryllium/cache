<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\CascadeClient;
use Beryllium\Cache\Client\MemoryClient;
use PHPUnit\Framework\TestCase;

class CascadeTest extends TestCase
{
    protected $client1;
    protected $client2;
    protected $cascade;

    public function setUp(): void
    {
        parent::setUp();

        $this->client1 = new MemoryClient();
        $this->client2 = new MemoryClient();
        $this->cascade = new CascadeClient(
            $this->client1,
            $this->client2
        );
    }

    public function testSimpleSetAndGet()
    {
        $this->cascade->set('test1', 'working');
        $this->assertSame('working', $this->client1->get('test1'));
        $this->assertSame('working', $this->client2->get('test1'));
        $this->assertSame('working', $this->cascade->get('test1'));
    }

    public function testCascadeWithoutBackfill()
    {
        $this->client2->set('test2', 'works');
        $this->assertTrue($this->cascade->has('test2'));
        $this->assertNull($this->client1->get('test2'));
        $this->assertSame('works', $this->client2->get('test2'));
        $this->assertSame('works', $this->cascade->get('test2'));
        $this->assertNull($this->client1->get('test2'));
    }

    public function testCascadeWithBackfill()
    {
        $this->cascade->enableBackfill();

        $this->client2->set('test3', 'works');
        $this->assertTrue($this->cascade->has('test3'));
        $this->assertNull($this->client1->get('test3'));
        $this->assertSame('works', $this->client2->get('test3'));
        $this->assertSame('works', $this->cascade->get('test3'));
        $this->assertSame('works', $this->client1->get('test3'));
    }

    public function testDelete()
    {
        $this->cascade->set('test1', 'working');
        $this->assertSame('working', $this->client1->get('test1'));
        $this->assertSame('working', $this->client2->get('test1'));
        $this->assertSame('working', $this->cascade->get('test1'));

        $this->cascade->delete('test1');
        $this->assertNull($this->client1->get('test1'));
        $this->assertNull($this->client2->get('test1'));
        $this->assertNull($this->cascade->get('test1'));
    }

    public function testClear()
    {
        $this->cascade->set('test1', 'working');
        $this->assertSame('working', $this->client1->get('test1'));
        $this->assertSame('working', $this->client2->get('test1'));
        $this->assertSame('working', $this->cascade->get('test1'));

        $this->cascade->clear();
        $this->assertNull($this->client1->get('test1'));
        $this->assertNull($this->client2->get('test1'));
        $this->assertNull($this->cascade->get('test1'));
    }

    public function testSetAndGetMultiple()
    {
        $data = [
            'test1' => 'working',
            'test2' => 'works',
            'test3' => 'great',
        ];

        $this->cascade->setMultiple($data);

        $this->assertSame($data, $this->cascade->getMultiple(array_keys($data)));

        $this->client1->delete('test2');

        // move test2 to the end of the array to match the expected response
        $expected = array_diff_key($data, ['test2' => false]) + ['test2' => 'works'];
        $this->assertSame($expected, $this->cascade->getMultiple(array_keys($data)));
    }

    public function testDeleteMultiple()
    {
        $data = [
            'test1' => 'working',
            'test2' => 'works',
            'test3' => 'great',
        ];

        $this->cascade->setMultiple($data);

        $this->assertSame($data, $this->cascade->getMultiple(array_keys($data)));

        $this->cascade->deleteMultiple(['test2', 'test3']);

        $this->assertSame(['test1' => 'working'], $this->cascade->getMultiple(array_keys($data)));
        $this->assertSame(['test1' => 'working'], array_filter($this->client1->getMultiple(array_keys($data))));
        $this->assertSame(['test1' => 'working'], array_filter($this->client2->getMultiple(array_keys($data))));
    }
}