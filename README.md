# Scout Elasticsearch Driver

This package offers an advanced functionality for searching and filtering data in Elasticsearch.
Check out its [features](#features)!

## Contents

* [Features](#features)
* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Index configurator](#index-configurator)
* [Searchable model](#searchable-model)
* [Usage](#usage)
* [Console commands](#console-commands)
* [Search rules](#search-rules)
* [Available filters](#available-filters)

## Features

* An easy way to [configure](#index-configurator) and [create](#console-commands) an Elasticsearch index.
* A fully configurable mapping for each [model](#searchable-model).
* A possibility to add a new field to an existing mapping [automatically](#installation) or using [the artisan command](#console-commands).
* Lots of different ways to implement your search algorithm: using [search rules](#search-rules) or a [raw search](#usage).
* [Various filter types](#available-filters) to make a search query more specific.

## Requirements

The package has been tested on following configuration: 

* PHP version &gt;= 7.0
* Laravel Framework version &gt;= 5.4
* Elasticsearch version &gt;= 5.2

## Installation

To install the package you can use composer:

```
composer require babenkoivan/scout-elasticsearch-driver
```

Once you've installed the package, you need to register the service provider in the `config/app.php` file:

```php
'providers' => [
    ScoutElastic\ScoutElasticServiceProvider::class    
]
``` 

## Configuration

To configure the package you need to publish settings first:

```
php artisan vendor:publish --provider=ScoutElastic\\ScoutElasticServiceProvider
```

There are two available options in the `config/scout_elastic.php` file:

Option | Description
--- | ---
client | A setting hash to build Elasticsearch client. The more information you can find [here](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_building_the_client_from_a_configuration_hash). By default the host set to `localhost:9200`.
update_mapping | The option that specifies whether to update a mapping automatically or not. By default it's set to `true`.

## Index configurator

An index configurator class is used to determine settings for an Elasticsearch index.
To create a new index configurator use the following artisan command:

```
php artisan make:index-configurator MyIndexConfigurator
```

It'll be created in the `app` folder of your project. 
You can specify index name, settings and default mapping like in the following example:

```php
<?php

namespace App;

use ScoutElastic\IndexConfigurator;

class MyIndexConfigurator extends IndexConfigurator
{
    // It's not obligatory to determine name. By default it'll be a snaked class name without `IndexConfigurator` part.
    protected $name = 'my_index';  
    
    // You can specify any settings you want, for example, analyzers. 
    protected $settings = [
        'analysis' => [
            'analyzer' => [
                'es_std' => [
                    'type' => 'standard',
                    'stopwords' => '_spanish_'
                ]
            ]    
        ]
    ];

    // Common mapping for all types.
    protected $defaultMapping = [
        '_all' => [
            'enabled' => true
        ],
        'dynamic_templates' => [
            [
                'es' => [
                    'match' => '*_es',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'analyzer' => 'es_std'
                    ]
                ]    
            ]
        ]
    ];
}
```

More about index settings and default mapping you can find in the [index management](https://www.elastic.co/guide/en/elasticsearch/guide/current/index-management.html) section of Elasticsearch documentation.

To create an index just run the artisan command:
 
```
php artisan elastic:create-index App\\MyIndexConfigurator
```