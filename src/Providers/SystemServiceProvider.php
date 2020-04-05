<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 2017/9/22
 */

namespace QL\Providers;

use QL\Contracts\ServiceProviderContract;
use QL\Kernel;
use Closure;
use QL\QueryList;

class SystemServiceProvider implements ServiceProviderContract
{
    public function register(Kernel $kernel)
    {
        $kernel->bind('html',function (...$args){
            if(empty($args)){
                /* @var QueryList $this */
                return $this->getHtml();
            }
            /* @var QueryList $this */
            $this->setHtml(...$args);
            return $this;
        });

//        $kernel->bind('queryData',function (Closure $callback = null){
//            return $this->query()->getData($callback)->all();
//        });

        $kernel->bind('pipe',function (Closure $callback = null){
            return $callback($this);
        });

    }
}