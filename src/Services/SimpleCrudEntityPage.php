<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace DelocalZrt\SimpleCrud\Services;


use DelocalZrt\SimpleCrud\Contracts\FieldCollectionInterface;
use DelocalZrt\SimpleCrud\Contracts\SimpleCrudEntityPageInterface;

class SimpleCrudEntityPage implements FieldCollectionInterface, SimpleCrudEntityPageInterface
{

    /** @var  CrudEvent $crudEvent */
    protected $crudEvent;
    protected $currentEntity;

    protected $fields = [];
    protected $actions = [];
    protected $title = '';
    protected $description = '';
    protected $footer = '';

    /**
     * SimpleCrudEntityPage constructor.
     * @param CrudEvent $crudEvent
     */
    public function __construct(CrudEvent $crudEvent)
    {
        $this->crudEvent = $crudEvent;
    }


    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getTitle(): string
    {
        return $this->title ?? '';
    }


    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }


    public function getField($fieldName)
    {
        return $this->fields[$fieldName] ?? null;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }


    public function setField(string $fieldName, $content)
    {
        $this->fields[$fieldName] = $content;
    }

    public function removeField(string $fieldName)
    {
        if (key_exists($fieldName, $this->fields)) {
            unset($this->fields[$fieldName]);
        }
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getAction(string $name)
    {
        return $this->actions[$name] ?? null;
    }

    public function addAction(string $name, array $action)
    {
        if (isset($action['action'])) {


            $this->actions[$name] = '<a class="btn btn-md btn-default"
                           id="action-999"
                           data-title="' . $action['name'] . '"
                           data-table-id-name="' . $this->crudEvent->getEloquentClassName() . '"
                           data-url="' . $action['action'] . '"
                           data-warning-text="' . ($action['warning'] ?? '') . '"
                           data-count-elem=""
                           data-ok-button-label="Igen"
                           data-ok-button-value="Mégse"
                           data-cancel-button-label="Mégse"
                           data-keyboard="true"
                           data-toggle="modal"
                           data-target="#Modal-Simplecrud"
                           href="#Modal-Simplecrud"
               ><span class="glyphicon glyphicon-' . ($action['icon'] ?? 'flash') . '"></span>
                ' . $action['name'] . '
                </a>';

        } elseif (isset($action['href'])) {
            $this->actions[$name] = '<a class="btn btn-md btn-default"
                        href="' . $action['href'] . '">
                        <span class="glyphicon glyphicon-' . ($action['icon'] ?? 'flash') . '"></span>
            ' . $action['name'] . '
            </a> ';
        }
    }

    public function removeAction(string $name)
    {
        if (key_exists($name, $this->actions)) {
            unset($this->actions[$name]);
        }
    }


    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    /**
     * @param string $footer
     */
    public function setFooter(string $footer)
    {
        $this->footer = $footer;
    }


}
