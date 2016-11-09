<?php

namespace Sofa\AutoPresenter;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class Decorator
{
    /** @var string[] Namepsaces checked for proprietary presenters */
    protected $proprietary_namespaces = ['App\\Presenters\\'];

    /** @var string Generic presenter, that will be used if no proprietary presenter is found */
    protected $generic_presenter = Presenter::class;

    /**
     * Set generic presenter class.
     *
     * @param string $class
     */
    public function setGenericPresenter($class)
    {
        $this->generic_presenter = $class;
    }

    /**
     * Add namespace for proprietary presenter lookup.
     *
     * @param string $namespace
     */
    public function addNamespace($namespace)
    {
        array_unshift($this->proprietary_namespaces, $namespace);
    }

    /**
     * Decorate model with presenter class.
     *
     * @param  mixed $model
     * @return \Sofa\AutoPresenter\Presenter
     */
    public function decorate($model)
    {
        if ($model instanceof Collection || is_array($model) || $model instanceof Paginator) {
            return $this->decorateMany($model);
        }

        if (!$model instanceof Presentable) {
            return $model;
        }

        $presenter = $this->generic_presenter;

        foreach ($this->proprietary_namespaces as $ns) {
            if (class_exists($proprietary = $ns.class_basename($model))) {
                $presenter = $proprietary;
                break;
            }
        }

        return new $presenter($model, $this);
    }

    public function decorateMany($models)
    {
        foreach ($models as $key => $model) {
            if ($model instanceof Presentable) {
                $models[$key] = $this->decorate($model);
            }
        }

        return $models;
    }
}
