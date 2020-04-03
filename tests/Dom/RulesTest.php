<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 18/12/12
 * Time: 下午12:25
 */

namespace QLTests\Dom;

use QL\QueryList;
use QLTests\TestCaseBase;
use Tightenco\Collect\Support\Collection;

class RulesTest extends TestCaseBase
{
    protected $html;
    protected $ql;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-2');
        $this->ql   = new QueryList($this->html);
    }

    /**
     * @test
     */
    public function get_data_by_rules()
    {
        $range = 'ul > li';

        $rules = [
            ['a', 'text', 'a'],
            ['img', 'src', 'img_src'],
            ['img', 'alt', 'img_alt']
        ];


        $data = $this->ql->range($range)->extract($rules);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(3, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data[1]['img_src'][0]);
    }


    /**
     * @test
     */
    public function get_data_by_rules_without_range()
    {
        $rules = [
            ['a', 'text', 'a_txt'],
            ['img', 'src', 'img_src'],
            ['img', 'alt', 'img_alt'],
        ];

        $data = $this->ql->extract($rules);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(1, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data[0]['img_src'][1]);
    }


    /**
     * @test
     */
    public function get_data_by_array_rules()
    {
        $range = 'ul>li';

        $rules = [
            [
                'test_selector' => 'a',
                'test_attr'     => 'text',
                'test_name'     => 'a'
            ],
            [
                'test_selector' => 'img',
                'test_attr'     => 'src',
                'test_name'     => 'img_src'
            ],
            [
                'test_selector' => 'img',
                'test_attr'     => 'alt',
                'test_name'     => 'img_alt'
            ],
        ];

        $data = $this->ql->range($range)->extract($rules, 'test_selector', 'test_attr', 'test_name');

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(3, $data);
        $this->assertEquals('http://querylist.com/2.jpg', $data[1]['img_src'][0]);
    }
}