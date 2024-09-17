<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:21
 */

namespace Endorbit\SimpleCrud\Contracts;


use Endorbit\SimpleCrud\Services\CrudEvent;

interface SimpleCrudEntityPageInterface extends FieldCollectionInterface
{
    public function __construct(CrudEvent $crudEvent);

    public function getActions();

    public function getAction(string $name);

    public function addAction(string $name, array $action);

    public function removeAction(string $name);

    public function getTitle();

    public function setTitle(string $title);

    public function setDescription(string $description);

    public function getDescription(): string;

    public function getFooter(): string;

    public function setFooter(string $footer);

}
