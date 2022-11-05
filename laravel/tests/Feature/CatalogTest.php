<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndex()
    {
        $response = $this->get('wx/catalog/index');
        dd($response->getOriginalContent());
    }

    public function testCurrent()
    {
        $response = $this->get('wx/catalog/current?id=1005000');
        dd($response->getOriginalContent());
    }
}
