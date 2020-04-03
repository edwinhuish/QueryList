<?php

namespace Tests\Dom;

use QL\Handlers\AbsoluteUrlsHandler;
use QL\QueryList;
use Tests\TestCaseBase;

class HandlersTest extends TestCaseBase
{
    public function testAbsUrlsHandler()
    {
        $html = <<<HTML
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

        $ql = QueryList::handle(AbsoluteUrlsHandler::class, 'http://www.querylist.cc/home.html')->setHtml($html);
        /* @var \QL\QueryList $ql */
        $data = $ql->extract([
            ['a', 'href', 'a_href'],
            ['img', 'src', 'img_src'],
        ]);

        $this->assertEquals('http://www.querylist.cc/', $data['a_href'][0]);
        $this->assertEquals('http://www.querylist.cc/doc/readme.html', $data['a_href'][1]);
        $this->assertEquals('http://v4.querylist.cc', $data['a_href'][2]);

        $this->assertEquals('http://www.querylist.cc/1.jpg', $data['img_src'][0]);
        $this->assertEquals('http://www.querylist.cc/2.jpg', $data['img_src'][1]);
        $this->assertEquals('http://querylist.com/3.jpg', $data['img_src'][2]);

    }
}