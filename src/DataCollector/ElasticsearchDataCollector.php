<?php

namespace ScoutElastic\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use Laravel\Scout\EngineManager;
use ScoutElastic\ElasticEngine;

class ElasticsearchDataCollector extends DataCollector implements DataCollectorInterface, Renderable
{
    /**
     * @var ElasticEngine
     */
    private $elasticEngine;

    /**
     * ElasticsearchDataCollector constructor.
     * @param EngineManager $elasticEngine
     */
    public function __construct(EngineManager $elasticEngine)
    {
        $this->elasticEngine = $elasticEngine;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'elastic-search';
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {

        $result = $this->elasticEngine->getResult();
        $query  = $this->elasticEngine->getQuery();

        return array_filter([
            'query'      => json_encode($query),
            'total hits' => array_get($result, 'hits.total'),
            'time took'  => array_get($result, 'took'),
        ]);
    }

    public function getWidgets()
    {
        $widgets = [
            "Elastic Search" => [
                "icon"    => "search",
                "widget"  => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map"     => "elastic-search",
                "default" => "{}"
            ]
        ];

        return $widgets;
    }
}
