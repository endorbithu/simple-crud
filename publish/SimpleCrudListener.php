<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 19.
 * Time: 13:37
 */

namespace App\SimpleCrud;


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

class SimpleCrudListener implements SimpleCrudListenerInterface
{

    //Minden Crud művelet/oldal előtt lefut, SimpleCrudPermissionDeniedException -t lehet dobni ha nincs jogosultság
    //ha message is van megadva, ki lesz íra hibasávba
    //az egyes Entitás listenerekben is lehet még tovább specifikálni a crudPermission -t
    public function checkPermission(CrudPermissionEvent $event): void
    {
        //if (!auth()->check()) throw new SimpleCrudPermissionDeniedException('Nincs jogosultság az oldal megtekintéséhez!');
    }

    public function preparingDatatable(CrudPreparingDatatableEvent $event): void
    {
    }

    public function preparingQueryBuilderForDatatable(CrudPreparingQueryBuilderForDatatableEvent $event): void
    {


    }

    public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void
    {
    }

    public function beforeShowEntity(CrudBeforeShowEntityEvent $event): void
    {
    }

    public function beforeRenderForm(CrudBeforeRenderFormEvent $event): void
    {
    }

    public function beforeSave(CrudBeforeSaveEvent $event): void
    {
    }

    public function afterSave(CrudAfterSaveEvent $event): void
    {
    }

    public function afterCreated(CrudAfterCreatedEvent $event): void
    {
    }

    public function afterUpdated(CrudAfterUpdatedEvent $event): void
    {
    }

    public function beforeDelete(CrudBeforeDeleteEvent $event): void
    {
    }

    public function afterDelete(CrudAfterDeleteEvent $event): void
    {
    }


}
