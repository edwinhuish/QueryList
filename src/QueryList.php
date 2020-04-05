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

use QL\Dom\Document;
use QL\Dom\Query;
use Tightenco\Collect\Support\Collection;
use Closure;
use QL\Services\MultiRequestService;


/**
 * Class QueryList
 * @package QL
 *
 * @method string getHtml()
 * @method QueryList setHtml($html)
 * @method QueryList html($html = null)
 * @method Dom\Elements find($selector)
 * #method QueryList rules(array $rules)
 * @method QueryList range($range)
 * #method QueryList removeHead()
 * #method QueryList query(Closure $callback = null)
 * #method Collection getData(Closure $callback = null)
 * #method Array queryData(Closure $callback = null)
 * #method QueryList setData(Collection $data)
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
 */
class QueryList
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var QueryList|null
     */
    protected static $instance = null;

    /**
     * QueryList constructor.
     */
    public function __construct()
    {
        $this->query  = new Query($this);
        $this->kernel = (new Kernel($this))->bootstrap();
        Config::getInstance()->bootstrap($this);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name)) {
            $result = $this->query->$name(...$arguments);
        } else {
            $result = $this->kernel->getService($name)->call($this, ...$arguments);
        }
        return $result;
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

}