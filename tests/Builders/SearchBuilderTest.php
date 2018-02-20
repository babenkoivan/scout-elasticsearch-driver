<?php

namespace ScoutElastic\Tests\Builders;

use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\SearchRule;

class SearchBuilderTest extends AbstractBuilderTest
{
    protected function initBuilder()
    {
        $this->builder = $this
            ->getMockBuilder(SearchBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testRule()
    {
        $ruleFunc = function(SearchBuilder $builder) {
            return [
                'must' => [
                    'match' => [
                        'foo' => $builder->query
                    ]
                ]
            ];
        };

        $this
            ->builder
            ->rule(SearchRule::class)
            ->rule($ruleFunc);

        $this->assertEquals(
            [
                SearchRule::class,
                $ruleFunc
            ],
            $this->builder->rules
        );
    }
}