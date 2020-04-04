<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 2017/9/22
 */

namespace QL;

use Closure;
use QL\Handlers\HtmlCharsetHandler;

class Config
{
    /**
     * @var Config
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $plugins = [];

    /**
     * @var array
     */
    protected $binds = [];


    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $defaultHandlers = [
        HtmlCharsetHandler::class => [],
    ];

    /**
     * @var bool
     */
    protected $disableDefault = false;

    /**
     * Get the Config instance
     *
     * @return null|Config
     */
    public static function getInstance()
    {
        self::$instance || self::$instance = new self();
        return self::$instance;
    }

    /**
     * Global installation plugin
     *
     * @param  string|array  $plugins
     * @param  array  ...$opt
     * @return $this
     */
    public function use($plugins, ...$opt)
    {
        if (is_string($plugins)) {
            $this->plugins[] = [$plugins, $opt];
        } else {
            $this->plugins = array_merge($this->plugins, $plugins);
        }
        return $this;
    }

    /**
     * Global binding custom method
     *
     * @param  string  $name
     * @param  Closure  $provider
     * @return $this
     */
    public function bind(string $name, Closure $provider)
    {
        $this->binds[$name] = $provider;
        return $this;
    }

    /**
     * Global handlers
     *
     * @param  string  $handler_class
     * @param  mixed  ...$args
     * @return $this
     */
    public function handle(string $handler_class, ...$args)
    {
        $this->handlers[$handler_class] = $args;
        return $this;
    }

    public function disableDefault()
    {
        $this->disableDefault = true;
        return $this;
    }

    public function bootstrap(QueryList $queryList)
    {
        $this->installPlugins($queryList)
            ->installBind($queryList)
            ->installHandlers($queryList);
    }

    protected function installPlugins(QueryList $queryList)
    {
        foreach ($this->plugins as $plugin) {
            if (is_string($plugin)) {
                $queryList->use($plugin);
            } else {
                $queryList->use($plugin[0], ...$plugin[1]);
            }
        }

        return $this;
    }

    protected function installBind(QueryList $queryList)
    {
        foreach ($this->binds as $name => $provider) {
            $queryList->bind($name, $provider);
        }

        return $this;
    }

    protected function installHandlers(QueryList $queryList)
    {
        $handlers = $this->disableDefault ? $this->handlers : array_merge($this->defaultHandlers, $this->handlers);

        foreach ($handlers as $handler => $args) {
            $queryList->handle($handler, ...$args);
        }

        return $this;
    }

}