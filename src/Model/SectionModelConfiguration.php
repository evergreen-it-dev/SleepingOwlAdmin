<?php

namespace SleepingOwl\Admin\Model;

use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Contracts\DisplayInterface;

class SectionModelConfiguration extends ModelConfigurationManager
{
    /**
     * @var array
     */
    protected $redirect = ['edit' => 'edit', 'create' => 'edit'];

    /**
     * @param string $redirect
     * @return void
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return collect($this->redirect);
    }

    /**
     * @return bool
     */
    public function isCreatable()
    {
        return method_exists($this, 'onCreate') && parent::isCreatable($this->getModel());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function isEditable(Model $model)
    {
        return method_exists($this, 'onEdit') && parent::isEditable($model);
    }

    /**
     * @param string $action
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function can($action, Model $model)
    {
        if (! $this->checkAccess) {
            return true;
        }

        return $this->gate->allows($action, [$this, $model]);
    }

    /**
     * @return DisplayInterface|mixed
     */
    public function fireDisplay()
    {
        if (! method_exists($this, 'onDisplay')) {
            return;
        }

        $display = $this->app->call([$this, 'onDisplay']);

        if ($display instanceof DisplayInterface) {
            $display->setModelConfiguration($this);
            $display->initialize();
        }

        return $display;
    }

    /**
     * @return mixed|void
     */
    public function fireCreate()
    {
        if (! method_exists($this, 'onCreate')) {
            return;
        }

        $form = $this->app->call([$this, 'onCreate']);
        if ($form instanceof DisplayInterface) {
            $form->setModelConfiguration($this);
        }

        if ($form instanceof Initializable) {
            $form->initialize();
        }

        if ($form instanceof FormInterface) {
            $form->setAction($this->getStoreUrl());
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function fireEdit($id)
    {
        if (! method_exists($this, 'onEdit')) {
            return;
        }

        $form = $this->app->call([$this, 'onEdit'], ['id' => $id]);
        if ($form instanceof DisplayInterface) {
            $form->setModelConfiguration($this);
        }

        if ($form instanceof Initializable) {
            $form->initialize();
        }

        if ($form instanceof FormInterface) {
            $form->setAction($this->getUpdateUrl($id));
            $form->setId($id);
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function fireDelete($id)
    {
        if (method_exists($this, 'onDelete')) {
            return $this->app->call([$this, 'onDelete'], ['id' => $id]);
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function fireDestroy($id)
    {
        if (method_exists($this, 'onDestroy')) {
            return $this->app->call([$this, 'onDestroy'], ['id' => $id]);
        }
    }

    /**
     * @param $id
     *
     * @return bool|mixed
     */
    public function fireRestore($id)
    {
        if (method_exists($this, 'onRestore')) {
            return $this->app->call([$this, 'onRestore'], ['id' => $id]);
        }
    }
}
