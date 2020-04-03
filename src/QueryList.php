<?php
/**
 * QueryList
 *
 * 一个基于phpQuery的通用列表采集类
 *
 * @author            Jaeger
 * @email            JaegerCode@gmail.com
 * @link            https://github.com/jae-jae/QueryList
 * @version         4.0.0
 *
 */

namespace QL;

use Exception;
use QL\Contracts\DocumentHandlerContract;
use QL\Contracts\ElementHandlerContract;
use QL\Contracts\HtmlHandlerContract;
use QL\Contracts\ResultHandlerContract;
use QL\Dom\Elements;
use Tightenco\Collect\Support\Collection;
use Closure;

/**
 * Class QueryList
 * @package QL
 *
 * @method string getHtml()
 * @method QueryList setHtml($html)
 * @method QueryList html($html)
 * @method Dom\Elements find($selector)
 * @method QueryList rules(array $rules)
 * @method QueryList range($range)
 * @method QueryList removeHead()
 * @method QueryList query(Closure $callback = null)
 * @method Collection getData(Closure $callback = null)
 * @method Array queryData(Closure $callback = null)
 * @method QueryList setData(Collection $data)
 * @method QueryList encoding(string $outputEncoding,string $inputEncoding = null)
 * @method QueryList get($url,$args = null,$otherArgs = [])
 * @method QueryList post($url,$args = null,$otherArgs = [])
 * @method QueryList postJson($url,$args = null,$otherArgs = [])
 * @method MultiRequestService multiGet($urls)
 * @method MultiRequestService multiPost($urls)
 * @method QueryList use($plugins,...$opt)
 * @method QueryList pipe(Closure $callback = null)
 */
class QueryList extends Elements
{

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var array
     */
    protected $handlers;

    /**
     * @var string
     */
    protected $range;

    protected static $instance = null;

    /**
     * QueryList constructor.
     *
     * @param  string|null  $html
     */
    public function __construct(string $html = null)
    {
        $html = $this->handleHtml($html);

        parent::__construct($html);

        $this->handleDocument();

        $this->kernel = (new Kernel($this))->bootstrap();
        Config::getInstance()->bootstrap($this);
    }

    /**
     * @param  string|null  $range
     * @return $this
     */
    public function range(string $range = null)
    {
        $this->range = $range;

        return $this;
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
        $root = $this;

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

        $this->range = null; // clear range after extract

        return collect($data);
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

    public function __call($name, $arguments)
    {
        return $this->kernel->getService($name)->call($this, ...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

    /**
     * Get the QueryList single instance
     *
     * @return QueryList
     */
    public static function getInstance()
    {
        self::$instance || self::$instance = new self();
        return self::$instance;
    }

    /**
     * Get the Config instance
     * @return null|Config
     */
    public static function config()
    {
        return Config::getInstance();
    }

    /**
     * Bind a custom method to the QueryList object
     *
     * @param  string  $name  Invoking the name
     * @param  Closure  $provide  Called method
     * @return $this
     */
    public function bind(string $name, Closure $provide)
    {
        $this->kernel->bind($name, $provide);
        return $this;
    }



    public function handle($handler, ...$args): QueryList
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

        return $this;
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

            call_user_func([$handler, 'handle'], $this, ...$args);
        }
    }

    /**
     * @param  Elements  $element
     * @param  array  $rule
     * @return Elements
     */
    protected function handleElement(Elements $element, array $rule = []): Elements
    {
        foreach ($this->getHandlers('element') as $handler => $args) {

            $element = call_user_func([$handler, 'handle'], $element, $rule, ...$args);
        }

        return $element;
    }

    /**
     * @param  array  $result
     * @param  array  $rule
     * @return array
     */
    protected function handleResult(array $result, array $rule = [])
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

}
