<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 2017/9/21
 */

namespace QL\Dom;

use Exception;
use QL\Contracts\DocumentHandlerContract;
use QL\Contracts\ElementHandlerContract;
use QL\Contracts\HtmlHandlerContract;
use QL\Contracts\ResultHandlerContract;
use QL\QueryList;
use Tightenco\Collect\Support\Collection;

class Query
{
    /**
     * @var string
     */
    protected $html;

    /**
     * @var Document
     */
    protected $document;

    /**
     * @var string|null
     */
    protected $range = null;

    /**
     * @var array
     */
    protected $handlers;

    /**
     * @var QueryList
     */
    protected $ql;

    public function __construct(QueryList $ql)
    {
        $this->ql = $ql;
    }

    /**
     * @return mixed
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param  string  $html
     * @return QueryList
     */
    public function setHtml(string $html)
    {
        $this->html = $html;

        $newHtml = $this->changeHtmlCharsetToUTF8($html);

        $this->handleHtml($newHtml);

        $this->document = new Document($newHtml);

        $this->handleDocument();

        return $this->ql;
    }

    /**
     * Searches for all elements that match the specified expression.
     *
     * @param  string  $selector  A string containing a selector expression to match elements against.
     * @return Elements
     */
    public function find($selector)
    {
        return $this->getDocument()->find($selector);
    }

    /**
     * @param  iterable  $rules
     * @param  string|int|null  $rule_selector_key
     * @param  string|int|null  $rule_attr_key
     * @param  string|int|null  $rule_name_key
     * @return Collection
     */
    public function extract(iterable $rules, $rule_selector_key = null, $rule_attr_key = null, $rule_name_key = null)
    {
        $root = $this->getDocument();

        if ( ! empty($this->range)) {
            $root = $root->find($this->range);
        }

        $data = [];
        $i    = 0;

        $root->map((function (Elements $element) use (&$data, &$i, $rules, $rule_selector_key, $rule_attr_key, $rule_name_key) {

            foreach ($rules as $idx => $rule) {

                [$selector, $attr, $name] = $this->getRuleAttributes($rule, $idx, $rule_selector_key, $rule_attr_key, $rule_name_key);

                $results = $element->find($selector)->map((function (Elements $element) use ($rule, $attr) {

                    return $this->handleElement($element, $rule)->{$attr};

                })->bindTo($this))->toArray();

                $data[$i][$name] = $this->handleResult($results, $rule);

            }

            $i++;
        })->bindTo($this));

        if (empty($this->range)) {
            $data = array_shift($data);
        }

        $this->range = null; // clear range after extract

        return new Collection($data);
    }

    /**
     * @param  object|array  $rule
     * @param  string|int  $idx
     * @param  string|int|null  $rule_selector_key
     * @param  string|int|null  $rule_attr_key
     * @param  string|int|null  $rule_name_key
     * @return array
     * @throws Exception
     */
    protected function getRuleAttributes($rule, $idx, $rule_selector_key = null, $rule_attr_key = null, $rule_name_key = null)
    {
        if (is_object($rule)) {
            $selector = $rule->{$rule_selector_key};

            $attr = $rule->{$rule_attr_key};

            $name = $rule->{$rule_name_key};

        } else {

            $selector = $rule_selector_key ? ($rule[$rule_selector_key] ?? null) : array_shift($rule);

            $attr = $rule_attr_key ? ($rule[$rule_attr_key] ?? null) : array_shift($rule);

            $name = $rule_name_key ? ($rule[$rule_name_key] ?? null) : array_shift($rule);
        }

        if ( ! $selector) {
            throw new Exception('Selector is missing in rules index: '.$idx);
        }

        if ( ! $attr) {
            throw new Exception('Attr is missing in rules index: '.$idx);
        }

        $name = $name ?? $idx;

        return [$selector, $attr, $name];
    }

    /**
     * Set the slice area for crawl list
     *
     * @param  string  $selector
     * @return QueryList
     */
    public function range(string $selector)
    {
        $this->range = $selector;
        return $this->ql;
    }


    /**
     * @param $handler
     * @param  mixed  ...$args
     * @return QueryList
     */
    public function handle($handler, ...$args)
    {
        switch (true) {
            case (is_subclass_of($handler, HtmlHandlerContract::class)):
                $this->handlers['html'][$handler] = $args;
                break;
            case (is_subclass_of($handler, DocumentHandlerContract::class)):
                $this->handlers['document'][$handler] = $args;
                break;
            case (is_subclass_of($handler, ElementHandlerContract::class)):
                $this->handlers['element'][$handler] = $args;
                break;
            case (is_subclass_of($handler, ResultHandlerContract::class)):
                $this->handlers['result'][$handler] = $args;
                break;
        }

        return $this->ql;
    }

    /**
     * @param  string  $html
     * @return $this
     */
    protected function handleHtml(string $html): string
    {
        foreach ($this->getHandlers('html') as $handler => $args) {

            $html = call_user_func([$handler, 'handle'], $html, ...$args);
        }

        return $html;
    }

    protected function handleDocument()
    {
//        $document = $this->getDocument();

        foreach ($this->getHandlers('document') as $handler => $args) {

            call_user_func([$handler, 'handle'], $this->getDocument(), ...$args);
//            $document = call_user_func([$handler, 'handle'], $document, ...$args);
        }

//        $this->document = $document;

        return $this->ql;
    }

    /**
     * @param  Elements  $element
     * @param  array|object  $rule
     * @return Elements
     */
    protected function handleElement(Elements $element, $rule): Elements
    {
        foreach ($this->getHandlers('element') as $handler => $args) {

            $element = call_user_func([$handler, 'handle'], $element, $rule, ...$args);
        }

        return $element;
    }

    /**
     * @param  array  $result
     * @param  array|object  $rule
     * @return array
     */
    protected function handleResult(array $result, $rule)
    {
        foreach ($this->getHandlers('result') as $handler => $args) {

            $result = call_user_func([$handler, 'handle'], $result, $rule, ...$args);
        }

        return $result;
    }

    /**
     * @param  string  $name
     * @return array
     */
    protected function getHandlers(string $name): array
    {
        return $this->handlers[$name] ?? [];
    }

    public function getDocument()
    {
        if ( ! $this->document) {
            $this->document = new Document();
        }

        return $this->document;
    }

    protected function changeHtmlCharsetToUTF8(string $html)
    {
        preg_match('/<meta[^>]+charset=([^"^\'^;^\s]*)[^>]*>/', $html, $matches);

        $charset = $matches[1] ?? 'auto';

        $newHtml = mb_convert_encoding($html, "UTF-8", $charset);

        return $charset === 'auto' ? $newHtml : preg_replace('/(<meta[^>]+)charset='.$matches[1].'([^>]*>)/', '${1}charset=UTF-8${2}', $newHtml);
    }

}
