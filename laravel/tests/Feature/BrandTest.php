<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use DatabaseTransactions;

    public function testList()
    {
        $response = $this->get('wx/brand/list');
        dd($response->getOriginalContent());
    }

    public function testDetail()
    {
        $response = $this->get('wx/brand/detail?id=1001000');
        dd($response->getOriginalContent());
    }
}
