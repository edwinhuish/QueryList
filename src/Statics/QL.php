<?php

namespace QL\Statics;

use Closure;
use QL\Config;
use QL\Dom\Document;
use QL\Dom\Elements;
use QL\QueryList;
use QL\Services\MultiRequestService;
use Tightenco\Collect\Support\Collection;

/**
 * Class QL
 * @package QL\Statics
 *
 * @method static QueryList bind(string $name, Closure $provide)
 * @method static string getHtml()
 * @method static QueryList setHtml($html)
 * @method static QueryList html($html = null)
 * @method static Elements find($selector)
 * #method static \QL\QueryList rules(array $rules)
 * @method static QueryList range($range)
 * #method static \QL\QueryList removeHead()
 * #method static \QL\QueryList query(Closure $callback = null)
 * #method static Collection getData(Closure $callback = null)
 * #method static Array queryData(Closure $callback = null)
 * #method static \QL\QueryList setData(Collection $data)
 * @method static QueryList encoding(string $outputEncoding, string $inputEncoding = null)
 * @method static QueryList get($url, $args = null, $otherArgs = [])
 * @method static QueryList post($url, $args = null, $otherArgs = [])
 * @method static QueryList postJson($url, $args = null, $otherArgs = [])
 * @method static MultiRequestService multiGet($urls)
 * @method static MultiRequestService multiPost($urls)
 * @method static QueryList use ($plugins, ...$opt)
 * @method static QueryList pipe(Closure $callback = null)
 *
 * @method static Config|null config()
 * @method static QueryList getInstance()
 *
 * @method static Collection extract(iterable $rules, string|int|null $rule_selector_key = null, string|int|null $rule_attr_key = null, string|int|null $rule_name_key = null)
 * @method static QueryList handle(string|Object $handler, ...$args)
 * @method static Document getDocument()
 *
 */
class QL
{

    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'config':
                return QueryList::config();
                break;
            case 'getInstance':
                return QueryList::getInstance();
                break;
        }

        return QueryList::getInstance()->$name(...$arguments);
    }
}