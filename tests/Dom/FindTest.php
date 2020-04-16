<?php
/**
 * Created by PhpStorm.
 * User: x
 * Date: 2018/12/10
 * Time: 12:46 AM
 */

namespace QL\Tests\Dom;


use QL\QueryList;
use QL\Tests\TestCaseBase;

class FindTest extends TestCaseBase
{
    protected $html;
    protected $ql;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-1');
        $this->ql   = QueryList::html($this->html);
    }

    /**
     * @test
     */
    public function find_first_dom_attr()
    {
        $img = [];
        $img[] = $this->ql->find('img')->attr('src');
        $img[] = $this->ql->find('img')->src;
        $img[] = $this->ql->find('div img:nth-child(1)')->src;
        $img[] = $this->ql->find('div img:eq(0)')->src;
        $img[] = $this->ql->find('img')->eq(-2)->src;

        $alt = $this->ql->find('img')->alt;
        $abc = $this->ql->find('img')->abc;

        $this->assertCount(1, array_unique($img));
        $this->assertEquals($alt, '这是图片');
        $this->assertEquals($abc, '这是一个自定义属性');

    }

    /**
     * @test
     */
    public function find_second_dom_attr()
    {

        $img2   = [];
        $img2[] = $this->ql->find('img')->eq(1)->alt;
        $img2[] = $this->ql->find('img:eq(1)')->alt;
        $img2[] = $this->ql->find('.second_pic')->alt;

        $this->assertCount(1, array_unique($img2));

    }

    /**
     * @test
     */
    public function find_dom_all_attr()
    {
        $imgAttr  = $this->ql->find('img:eq(0)')->attr('alt');
        $linkAttr = $this->ql->find('a:eq(1)')->attr('href');

        $this->assertEquals('这是图片', $imgAttr);
        $this->assertEquals('http://doc.querylist.cc', $linkAttr);
    }
}