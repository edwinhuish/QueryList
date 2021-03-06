<?php
/**
 * Created by PhpStorm.
 * User: x
 * Date: 2018/12/10
 * Time: 1:14 AM
 */

namespace QL\Tests\Feature;


use QL\QueryList;
use QL\Tests\TestCaseBase;

class MethodTest extends TestCaseBase
{
    protected $html;

    public function setUp()
    {
        $this->html = $this->getSnippet('snippet-1');
    }

    /**
     * @test
     */
    public function pipe()
    {
        $html = $this->html;
        $qlHtml = QueryList::pipe(function(QueryList $ql) use($html){
            $ql->setHtml($html);
            return $ql;
        })->getHtml();
        $this->assertEquals($html,$qlHtml);
    }
}