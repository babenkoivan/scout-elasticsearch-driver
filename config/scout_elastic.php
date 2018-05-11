<?php

return [
	
	/*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for Elasticsearch, which is a
    | distributed, open source search and analytics engine. Feel free
    | to add as many Elasticsearch servers as required by your app.
    |
    */
    'client' => [
        'hosts' => [
            env('SCOUT_ELASTIC_HOST', 'localhost:9200')
        ]
    ],

    'update_mapping' => env('SCOUT_ELASTIC_UPDATE_MAPPING', true),

    'indexer' => env('SCOUT_ELASTIC_INDEXER', 'single'),

    'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH', null),

    'track_scores' => env('SCOUT_ELASTIC_TRACK_SCORES', true)

];