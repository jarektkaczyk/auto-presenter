<?php

namespace Sofa\AutoPresenter;

use Illuminate\Contracts\View\Factory;

/**
 * This Provider registers a View Composer that will automatically
 * decorate Presentable models with appropriate Presenter class.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(Factory $view)
    {
        $view->composer('*', function ($view) {
            $data = array_merge($view->getFactory()->getShared(), $view->getData());

            foreach ($data as $var => $model) {
                $view[$var] = $this->app['presenters']->decorate($model);
            }
        }, 999);
    }

    public function register()
    {
        $this->app->singleton('presenters', function () {
            return new Decorator;
        });
    }
}
