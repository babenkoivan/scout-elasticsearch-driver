<?php

namespace ScoutElastic\Tests\Payloads;

use ScoutElastic\Tests\AbstractTestCase;
use ScoutElastic\Payloads\DocumentPayload;
use ScoutElastic\Tests\Dependencies\Model;

class DocumentPayloadTest extends AbstractTestCase
{
    use Model;

    public function testDefault()
    {
        $model = $this->mockModel();

        $payload = new DocumentPayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
            ],
            $payload->get()
        );
    }

    public function testSet()
    {
        $indexConfigurator = $this->mockIndexConfigurator([
            'name' => 'foo',
        ]);

        $model = $this->mockModel([
            'searchable_as' => 'bar',
            'index_configurator' => $indexConfigurator,
        ]);

        $payload = (new DocumentPayload($model))
            ->set('index', 'test_index')
            ->set('type', 'test_type')
            ->set('id', 2)
            ->set('body', []);

        $this->assertEquals(
            [
                'index' => 'foo',
                'type' => 'bar',
                'id' => 1,
                'body' => [],
            ],
            $payload->get()
        );
    }
}
