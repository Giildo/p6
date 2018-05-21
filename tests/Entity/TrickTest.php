<?php

namespace Tests\Entity;

use App\Entity\Trick;
use PHPUnit\Framework\TestCase;

class TrickTest extends TestCase
{
    /**
     * @var Trick
     */
    private $trick;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->trick = new Trick();
    }

    public function testSlugifyWithUppercase()
    {
        $this->trick->setName('Figure');

        $this->assertEquals('figure', $this->trick->getSlug());
    }

    public function testSlugifyWithSpace()
    {
        $this->trick->setName('essai de nom');

        $this->assertEquals('essai-de-nom', $this->trick->getSlug());
    }

    public function testSlugifyWithAccent()
    {
        $this->trick->setName('figure à rotation entière');

        $this->assertEquals('figure-a-rotation-entiere', $this->trick->getSlug());
    }

    public function testSlugifyWithApostrophe()
    {
        $this->trick->setName("essai avec l'apostrophe");

        $this->assertEquals('essai-avec-lapostrophe', $this->trick->getSlug());
    }
}
