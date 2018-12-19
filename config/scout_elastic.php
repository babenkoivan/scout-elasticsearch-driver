<?php

return [
    'client' => [
        'hosts' => [
            env('SCOUT_ELASTIC_HOST', 'localhost:9200')
        ]
    ],
    'update_mapping' => env('SCOUT_ELASTIC_UPDATE_MAPPING', true),
    'indexer' => env('SCOUT_ELASTIC_INDEXER', 'single'),
    'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH'),
	'uses_tablename_prefix_as_doc_id' =>env('SCOUT_ELASTIC_USES_TABLENAME_PREFIX_AS_DOC_ID', false)
];
