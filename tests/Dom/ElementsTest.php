<?php
/**
 * Created by PhpStorm.
 * User: x
 * Date: 2018/12/10
 * Time: 12:46 AM
 */

namespace QLTests\Dom;


use QL\Dom\Elements;
use QLTests\TestCaseBase;
use Tightenco\Collect\Support\Collection;

class ElementsTest extends TestCaseBase
{
    protected $html;
    protected $el;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-1');
        $this->el   = new Elements($this->html);
    }

    /**
     * @test
     */
    public function each_test()
    {
        $this->el->find('img')->each(function (Elements $el) {
            $el->attr('src', 'http://www.test.com');
        });

        $src = $img = $this->el->find('img')->offsetGet(0)->attr('src');

        $this->assertEquals($src, 'http://www.test.com');

    }

    /**
     * @test
     */
    public function map_test()
    {
        $data = $this->el->find('img')->map(function (Elements $el, $idx) {

            if ($idx === 0) {
                return null;
            }

            return $el->attr('src');
        });

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(1, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data->offsetGet(0));

    }

}