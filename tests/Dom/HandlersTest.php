<?php

namespace QL\Tests\Dom;

use QL\Handlers\AbsoluteUrlsHandler;
use QL\Handlers\MinifyHtmlHandler;
use QL\Handlers\OneAttrPerElementHandler;
use QL\QueryList;
use QL\Tests\TestCaseBase;

class HandlersTest extends TestCaseBase
{
    /**
     * @var string
     */
    protected $html;

    /**
     * @noinspection
     */
    protected function setUp()
    {
        $this->html = /** @lang text */
            <<<HTML
<div id="one">
    <ul>
        <li>
            <a href="/">QueryList官网</a>
            <img src="1.jpg" alt="这是图片1" abc="这是一个自定义属性1">
        </li>
        <li>
            <a href="/doc/readme.html">QueryList V3文档</a>
            <img src="/2.jpg" alt="这是图片2" abc="这是一个自定义属性2">
        </li>
        <li>
            <a href="http://v4.querylist.cc">QueryList V4文档</a>
            <img src="http://querylist.com/3.jpg" alt="这是图片3" abc="这是一个自定义属性3">
        </li>
    </ul>
</div>
HTML;

    }

    public function testAbsUrlsHandler()
    {

        $data = QueryList::handle(AbsoluteUrlsHandler::class, 'http://www.querylist.cc/home.html')
            ->setHtml($this->html)
            ->extract([
                'a_href'  => ['a', 'href'],
                'img_src' => ['img', 'src'],
            ]);

        $this->assertEquals('http://www.querylist.cc/', $data['a_href'][0]);
        $this->assertEquals('http://www.querylist.cc/doc/readme.html', $data['a_href'][1]);
        $this->assertEquals('http://v4.querylist.cc', $data['a_href'][2]);

        $this->assertEquals('http://www.querylist.cc/1.jpg', $data['img_src'][0]);
        $this->assertEquals('http://www.querylist.cc/2.jpg', $data['img_src'][1]);
        $this->assertEquals('http://querylist.com/3.jpg', $data['img_src'][2]);

    }


    public function testMinHtmlHandler()
    {
        /* @var \QL\QueryList $ql */
        $ql = QueryList::handle(MinifyHtmlHandler::class)->setHtml($this->html);

        $expected = /** @lang text */
            '<div id="one"> <ul> <li> <a href="/">QueryList官网</a> <img src="1.jpg" alt="这是图片1" abc="这是一个自定义属性1"> </li> <li> <a href="/doc/readme.html">QueryList V3文档</a> <img src="/2.jpg" alt="这是图片2" abc="这是一个自定义属性2"> </li> <li> <a href="http://v4.querylist.cc">QueryList V4文档</a> <img src="http://querylist.com/3.jpg" alt="这是图片3" abc="这是一个自定义属性3"> </li> </ul></div>';

        $this->assertEquals($expected, $ql->getDocument()->getOuterHtml());

    }

    public function testOneAttrPerElementHandler()
    {
        $data = QueryList::handle(OneAttrPerElementHandler::class)
            ->setHtml($this->html)
            ->extract([
                'a_href'  => ['a', 'href'],
                'img_src' => ['img', 'src'],
            ])->toArray();

        $this->assertCount(2, $data);
        $this->assertEquals('/', $data['a_href']);
        $this->assertEquals('1.jpg', $data['img_src']);
    }
}