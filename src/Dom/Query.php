<?php

namespace QL\Dom;

use Exception;
use QL\Contracts\HandleDocumentContract;
use QL\Contracts\HandleElementContract;
use QL\Contracts\HandleHtmlContract;
use QL\Contracts\HandleAttributesContract;
use QL\Contracts\HandleAttributeContract;
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
     * @param  bool  $getOriginal
     * @return string
     */
    public function getHtml(bool $getOriginal = false): string
    {
        if ($getOriginal && $this->document) {
            return $this->document->getOuterHtml();
        }

        return $this->html;
    }

    /**
     * @param  string  $html
     * @return QueryList
     */
    public function setHtml(string $html)
    {
        $this->html = $html;

        $newHtml = $this->handleHtml($html);

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
        $root = $this->getDocument()->find(':first');

        if ( ! empty($this->range)) {
            $root = $root->find($this->range);
        }

        $data = [];
        $i    = 0;

        $root->map((function (Elements $element) use (&$data, &$i, $rules, $rule_selector_key, $rule_attr_key, $rule_name_key) {

            foreach ($rules as $idx => $rule) {

                list($selector, $attr, $name) = $this->getRuleAttributes($rule, $idx, $rule_selector_key, $rule_attr_key, $rule_name_key);

                $results = $element->find($selector)->map((function (Elements $element) use ($rule, $attr) {

                    $result = $this->handleExtractedElement($element, $rule)->{$attr};

                    return $this->handleExtractedAttribute($result, $rule);

                })->bindTo($this))->toArray();

                $data[$i][$name] = $this->handleExtractedAttributes($results, $rule, $this->range);

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
     * @param  string  $handler_class
     * @param  mixed  ...$args
     * @return QueryList
     */
    public function handle(string $handler_class, ...$args)
    {
        switch (true) {
            case (is_subclass_of($handler_class, HandleHtmlContract::class)):
                $this->handlers['html'][$handler_class] = $args;
                break;
            case (is_subclass_of($handler_class, HandleDocumentContract::class)):
                $this->handlers['document'][$handler_class] = $args;
                break;
            case (is_subclass_of($handler_class, HandleElementContract::class)):
                $this->handlers['element'][$handler_class] = $args;
                break;
            case (is_subclass_of($handler_class, HandleAttributeContract::class)):
                $this->handlers['attr'][$handler_class] = $args;
                break;
            case (is_subclass_of($handler_class, HandleAttributesContract::class)):
                $this->handlers['attrs'] = [$handler_class => $args];
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
        foreach ($this->getHandlers('document') as $handler => $args) {

            call_user_func([$handler, 'handle'], $this->getDocument(), ...$args);
        }

        return $this->ql;
    }

    /**
     * @param  Elements  $element
     * @param  array|object  $rule
     * @return Elements
     */
    protected function handleExtractedElement(Elements $element, $rule): Elements
    {
        foreach ($this->getHandlers('element') as $handler => $args) {

            $element = call_user_func([$handler, 'handle'], $element, $rule, ...$args);
        }

        return $element;
    }

    /**
     * @param  string  $attr
     * @param  array|object  $rule
     * @return string
     */
    protected function handleExtractedAttribute(string $attr, $rule): string
    {
        foreach ($this->getHandlers('attr') as $handler => $args) {

            $attr = call_user_func([$handler, 'handle'], $attr, $rule, ...$args);
        }

        return $attr;
    }

    /**
     * @param  array  $attrs
     * @param  array|object  $rule
     * @param  string  $range
     * @return array|string|null|mixed
     */
    protected function handleExtractedAttributes(array $attrs, $rule, $range = null)
    {
        foreach ($this->getHandlers('attrs') as $handler => $args) {

            $attrs = call_user_func([$handler, 'handle'], $attrs, $rule, $range, ...$args);

            break; // just run the first loop
        }

        return $attrs;
    }

    /**
     * @param  string  $name
     * @return array
     */
    protected function getHandlers(string $name): array
    {
        return $this->handlers[$name] ?? [];
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        if ( ! $this->document) {
            $this->document = new Document();
        }

        return $this->document;
    }

}
