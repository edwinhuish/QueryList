<?php

namespace QL\Statics;

use Closure;
use QL\Dom\Document;
use QL\Dom\Elements;
use QL\QueryList;
use QL\Services\MultiRequestService;
use Tightenco\Collect\Support\Collection;

/**
 * Class QL
 * @package QL\Statics
 *
 * @method QueryList bind(string $name, Closure $provide)
 * @method string getHtml()
 * @method QueryList setHtml($html)
 * @method QueryList html($html)
 * @method Elements find($selector)
 * #method \QL\QueryList rules(array $rules)
 * @method QueryList range($range)
 * #method \QL\QueryList removeHead()
 * #method \QL\QueryList query(Closure $callback = null)
 * #method Collection getData(Closure $callback = null)
 * #method Array queryData(Closure $callback = null)
 * #method \QL\QueryList setData(Collection $data)
 * @method QueryList encoding(string $outputEncoding, string $inputEncoding = null)
 * @method QueryList get($url, $args = null, $otherArgs = [])
 * @method QueryList post($url, $args = null, $otherArgs = [])
 * @method QueryList postJson($url, $args = null, $otherArgs = [])
 * @method MultiRequestService multiGet($urls)
 * @method MultiRequestService multiPost($urls)
 * @method QueryList use ($plugins, ...$opt)
 * @method QueryList pipe(Closure $callback = null)
 *
 * @method Collection extract(iterable $rules, string|int|null $rule_selector_key = null, string|int|null $rule_attr_key = null, string|int|null $rule_name_key = null)
 * @method QueryList handle(string|Object $handler, ...$args)
 * @method Document getDocument()
 *
 */
class QL
{

    public static function newInstance()
    {
        return new QueryList();
    }

    public static function __callStatic($name, $arguments)
    {
        return static::newInstance()->$name(...$arguments);
    }
}