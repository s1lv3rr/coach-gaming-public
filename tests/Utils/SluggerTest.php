<?php

namespace App\Tests\Utils;

use App\Utils\Slugger;
use PHPUnit\Framework\TestCase;

class SluggerTest extends TestCase
{
        

    public function testSlugify() 
    {

        $slugger = new Slugger(false);

        $result = $slugger->slugify("Phrase Pour Le Test Unitaire");

        $this->assertEquals("Phrase-Pour-Le-Test-Unitaire", $result);               
        
    }
}