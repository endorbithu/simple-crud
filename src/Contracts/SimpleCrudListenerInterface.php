<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 19.
 * Time: 13:37
 */

namespace DelocalZrt\SimpleCrud\Contracts;


use DelocalZrt\SimpleCrud\Events\CrudAfterCreatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterUpdatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeRenderFormEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSendRowsToDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeShowEntityEvent;
use DelocalZrt\SimpleCrud\Events\CrudPermissionEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingQueryBuilderForDatatableEvent;

interface SimpleCrudListenerInterface
{

    public function checkPermission(CrudPermissionEvent $event): void;

    public function preparingDatatable(CrudPreparingDatatableEvent $event): void;

    public function preparingQueryBuilderForDatatable(CrudPreparingQueryBuilderForDatatableEvent $event): void;

    public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void;

    public function beforeShowEntity(CrudBeforeShowEntityEvent $event): void;

    public function beforeRenderForm(CrudBeforeRenderFormEvent $event): void;

    public function beforeSave(CrudBeforeSaveEvent $event): void;

    public function afterSave(CrudAfterSaveEvent $event): void;

    public function afterCreated(CrudAfterCreatedEvent $event): void;

    public function afterUpdated(CrudAfterUpdatedEvent $event): void;

    public function beforeDelete(CrudBeforeDeleteEvent $event): void;

    public function afterDelete(CrudAfterDeleteEvent $event): void;

}
