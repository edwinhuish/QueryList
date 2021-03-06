<?php
/**
 * Created by PhpStorm.
 * User: x
 * Date: 2018/12/9
 * Time: 11:10 PM
 */

namespace QL\Tests\Feature;


use QL\QueryList;
use QL\Tests\TestCaseBase;

class InstanceTest extends TestCaseBase
{
    protected $html;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-1');
    }
    /**
     * @test
     */
    public function singleton_instance_mode()
    {
        $ql = QueryList::getInstance()->html($this->html);
        $ql2 = QueryList::getInstance();
        $this->assertEquals($ql->getHtml(),$ql2->getHtml());


    }

    /**
     * @test
     */
    public function get_new_object()
    {
        $ql = QueryList::html($this->html);
        $ql2 = new QueryList();
        $this->assertNotEquals($ql->getHtml(),$ql2->getHtml());

        $ql = QueryList::range('')->html($this->html);
        $ql2 = QueryList::range('');
        $this->assertNotEquals($ql->getHtml(),$ql2->getHtml());
    }
}