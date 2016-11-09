<?php

namespace Sofa\AutoPresenter;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Routing\UrlRoutable;

class Presenter implements UrlRoutable
{
    /** @var \Sofa\AutoPresenter\Presentable */
    protected $model;

    /** @var \Sofa\AutoPresenter\Decorator */
    protected $decorator;

    public function __construct(Presentable $model, Decorator $decorator)
    {
        $this->model = $model;
        $this->decorator = $decorator;
    }

    public function raw($key = null)
    {
        return $raw ? $this->model->{$raw} : $this->model;
    }

    public function __get($key)
    {
        $value = $this->model->{$key};

        if ($value instanceof Presentable || $value instanceof Collection || is_array($value)) {
            return $this->decorator->decorate($value);
        }

        return $this->present($key);
    }

    protected function present($key)
    {
        return ($method = $this->getMethod($key))
                ? call_user_func([$this, $method], $this->model->{$key})
                : $this->model->{$key};
    }

    protected function getMethod($key)
    {
        if (method_exists($this, $method = $key)
         || method_exists($this, $method = Str::snake($key))
         || method_exists($this, $method = Str::camel($key))
        ) {
            return $method;
        }
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->model, $method], $params);
    }

    public function __isset($key)
    {
        return $this->model->__isset($key);
    }

    /**
     * Cast to array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = collect($this->model->toArray());

        return $attributes->map(function ($_, $key) {
            return $this->present($key);
        })->all();
    }

    /**
     * Cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->model->getRouteKey();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->model->getRouteKeyName();
    }
}
