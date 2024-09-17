<?php

namespace Endorbit\SimpleCrud\SimpleCrud;

use Endorbit\SimpleCrud\Contracts\SimpleCrudListenerInterface;
use Endorbit\SimpleCrud\Events\CrudAfterCreatedEvent;
use Endorbit\SimpleCrud\Events\CrudAfterDeleteEvent;
use Endorbit\SimpleCrud\Events\CrudAfterSaveEvent;
use Endorbit\SimpleCrud\Events\CrudAfterUpdatedEvent;
use Endorbit\SimpleCrud\Events\CrudBeforeDeleteEvent;
use Endorbit\SimpleCrud\Events\CrudBeforeRenderFormEvent;
use Endorbit\SimpleCrud\Events\CrudBeforeSaveEvent;
use Endorbit\SimpleCrud\Events\CrudBeforeSendRowsToDatatableEvent;
use Endorbit\SimpleCrud\Events\CrudBeforeShowEntityEvent;
use Endorbit\SimpleCrud\Events\CrudPermissionEvent;
use Endorbit\SimpleCrud\Events\CrudPreparingDatatableEvent;
use Endorbit\SimpleCrud\Events\CrudPreparingQueryBuilderForDatatableEvent;

class SimpleCrudActivityLog implements SimpleCrudListenerInterface
{

    public function checkPermission(CrudPermissionEvent $event): void {}

    public function preparingDatatable(CrudPreparingDatatableEvent $event): void
    {
        $event->getDatatable()->deleteAction('new');
        $event->getDatatable()->deleteAction('delete');
    }

    public function preparingQueryBuilderForDatatable(CrudPreparingQueryBuilderForDatatableEvent $event): void {}

    public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void {}

    public function beforeShowEntity(CrudBeforeShowEntityEvent $event): void {}

    public function beforeRenderForm(CrudBeforeRenderFormEvent $event): void {}

    public function beforeSave(CrudBeforeSaveEvent $event): void {}

    public function afterSave(CrudAfterSaveEvent $event): void {}

    public function afterCreated(CrudAfterCreatedEvent $event): void {}

    public function afterUpdated(CrudAfterUpdatedEvent $event): void {}

    public function beforeDelete(CrudBeforeDeleteEvent $event): void
    {
        throw new \Exception('Deleting SipleCrud Activity Log is not allowed!');
    }

    public function afterDelete(CrudAfterDeleteEvent $event): void {}


}
