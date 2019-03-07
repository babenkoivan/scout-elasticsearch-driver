<?php

namespace ScoutElastic\Tests;

use ScoutElastic\Highlight;

class HighlightTest extends AbstractTestCase
{
    public function testGetter()
    {
        $highlight = new Highlight([
            'title' => ['Title snippet 1'],
            'description' => ['Description snippet 1', 'Description snippet 2'],
        ]);

        $this->assertEquals(
            ['Title snippet 1'],
            $highlight->title
        );

        $this->assertEquals(
            'Description snippet 1 Description snippet 2',
            $highlight->descriptionAsString
        );
    }
}
