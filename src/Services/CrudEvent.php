<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace Endorbit\SimpleCrud\Services;


use Illuminate\Database\Eloquent\Model;

class CrudEvent
{
    protected $action;
    protected $eloquentClass;
    protected $eloquentClassName;
    protected $id;
    protected $entity;


    /**
     * @return Model
     */
    public function getEntity(): ?Model
    {
        return $this->entity;
    }

    /**
     * @param Model $entityEntity
     */
    public function setEntity(Model $entityEntity)
    {
        $this->entity = $entityEntity;
    }

    /**
     * @return mixed
     */
    public function getEloquentClass()
    {
        return $this->eloquentClass;
    }

    /**
     * @param mixed $eloquentClass
     */
    public function setEloquentClass($eloquentClass)
    {
        $this->eloquentClass = $eloquentClass;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEloquentClassName()
    {
        return $this->eloquentClassName;
    }

    /**
     * @param mixed $eloquentClassName
     */
    public function setEloquentClassName($eloquentClassName)
    {
        $this->eloquentClassName = $eloquentClassName;
    }


}
