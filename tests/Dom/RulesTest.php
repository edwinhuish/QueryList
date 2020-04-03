<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 18/12/12
 * Time: 下午12:25
 */

namespace Tests\Dom;


use QL\QueryList;
use Tests\TestCaseBase;
use Tightenco\Collect\Support\Collection;

class RulesTest extends TestCaseBase
{
    protected $html;
    protected $ql;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-2');
        $this->ql   = QueryList::html($this->html);
    }

    /**
     * @test
     */
    public function get_data_by_rules()
    {
        $rules = [
            ['a', 'text', 'a_text'],
            ['img', 'src', 'img_src'],
            ['img', 'alt', 'img_alt'],
        ];
        $range = 'ul>li';
        $ql    = new QueryList();
        $data  = $ql->range($range)->setHtml($this->html)->extract($rules);
        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(3, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data[1]['img_src'][0]);
    }


    /**
     * @test
     */
    public function get_data_by_eloquent_obj()
    {
        $obj1 = new \stdClass();
        $obj1->name='a_text';
        $obj1->selector = 'a';
        $obj1->attr = 'text';

        $obj2 = new class
        {
            public $name = 'img_src';
            public $selector = 'img';
            public $attr = 'src';
        };

        $obj3 = new class
        {
            protected $name = 'img_alt';
            protected $selector = 'img';
            protected $attr = 'alt';

            public function __get($name)
            {
                return $this->$name;
            }
        };

        $eloquent = [$obj1, $obj2, $obj3];
        $range = 'ul>li';
        $ql    = new QueryList();
        $data  = $ql->range($range)->setHtml($this->html)->extract($eloquent, 'selector', 'attr', 'name');
        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(3, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data[1]['img_src'][0]);
    }
}