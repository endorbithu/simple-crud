<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace Endorbit\SimpleCrud\Services;


use Endorbit\SimpleCrud\Contracts\FieldCollectionInterface;
use Endorbit\SimpleCrud\Contracts\SimpleCrudShowInterface;

class SavingRequestCollection implements FieldCollectionInterface
{
    /** @var  CrudEvent $crudEvent */
    protected $crudEvent;
    protected $currentEntity;

    /** @var array */
    protected $request;
    protected $fields = [];

    public function __construct($crudEvent, $request)
    {
        $this->fields = $request->all();
        $this->crudEvent = $crudEvent;

    }


    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): FieldCollectionInterface
    {
        $this->fields = $fields;
        return $this;
    }

    public function setField(string $fieldName, $content): FieldCollectionInterface
    {
        $this->fields[$fieldName] = $content;
        return $this;
    }

    public function getField($fieldName)
    {
        return $this->fields[$fieldName] ?? null;
    }

    public function removeField(string $fieldName)
    {
        if (key_exists($fieldName, $this->fields)) {
            unset($this->fields[$fieldName]);
        }


    }

}
