<?php

namespace ScoutElastic\Tests\Builders;

use ScoutElastic\SearchRule;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Tests\AbstractTestCase;
use ScoutElastic\Tests\Dependencies\Model;

class SearchBuilderTest extends AbstractTestCase
{
    use Model;

    public function testRule()
    {
        $builder = new SearchBuilder($this->mockModel(), 'qwerty');

        $ruleFunc = function (SearchBuilder $builder) {
            return [
                'must' => [
                    'match' => [
                        'foo' => $builder->query,
                    ],
                ],
            ];
        };

        $builder->rule(SearchRule::class)->rule($ruleFunc);

        $this->assertEquals([
            SearchRule::class,
            $ruleFunc,
        ], $builder->rules);
    }
}
