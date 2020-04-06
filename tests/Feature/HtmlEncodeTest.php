<?php

namespace Tests;

use QL\Handlers\HtmlCharsetHandler;
use QL\Statics\QL;

class HtmlEncodeTest extends TestCaseBase
{
    protected $html;

    protected function setUp()
    {
        $this->html = $this->getSnippet('snippet-3');
    }

    public function testCharsetHtml()
    {
        $text = QL::html($this->html)->find('h1')->text;

        $this->assertEquals('这是一个测试', $text);

    }

    public function testEncodingHtml()
    {
        $html = '<h1>这是一个测试</h1>';
        $html = mb_convert_encoding($html, 'UTF-16', 'UTF-8');  // change the $html encoding to 'UTF-16' for test.

        QL::handle(HtmlCharsetHandler::class, 'UTF-16'); // Let HtmlCharsetHandler recover from 'UTF-16' to 'UTF-8'

        $text = QL::html($html)->find('h1')->text;

        $this->assertEquals('这是一个测试', $text);
    }
}