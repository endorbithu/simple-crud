<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 19.
 * Time: 13:37
 */

namespace DelocalZrt\SimpleCrud\Listeners;


use DelocalZrt\SimpleCrud\Contracts\SimpleCrudListenerInterface;
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
use DelocalZrt\SimpleCrud\Models\SimpleCrudActivityLog;
use DelocalZrt\SimpleCrud\Services\SimpleCrudHelper;

class MainListener implements SimpleCrudListenerInterface
{

    public function checkPermission(CrudPermissionEvent $event): void
    {
        $met = __FUNCTION__;

        $this->runMainListener($met, $event);

        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }


    public function afterCreated(CrudAfterCreatedEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function afterDelete(CrudAfterDeleteEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function afterSave(CrudAfterSaveEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function afterUpdated(CrudAfterUpdatedEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function beforeDelete(CrudBeforeDeleteEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function beforeRenderForm(CrudBeforeRenderFormEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function beforeSave(CrudBeforeSaveEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function preparingQueryBuilderForDatatable(CrudPreparingQueryBuilderForDatatableEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    public function beforeShowEntity(CrudBeforeShowEntityEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }


    public function preparingDatatable(CrudPreparingDatatableEvent $event): void
    {
        $met = __FUNCTION__;
        $this->runMainListener($met, $event);
        $listener = $this->getActualListener($event);
        if ($listener) $listener->$met($event);
    }

    protected function getActualListener($event): ?object
    {
        $listenerClass = SimpleCrudHelper::getFullSimpleCrudClassFromClassBasename($event->getCrudEvent()->getEloquentClassName(), 'SimpleCrud');

        if (!($listenerClass)) return null;

        $listener = new $listenerClass();
        if ($listener instanceof SimpleCrudListenerInterface) {
            return $listener;
        }

        return null;
    }

    protected function runMainListener($action, $event)
    {
        $mainPermissionClass = 'App\\SimpleCrud\\SimpleCrudListener';
        if (class_exists($mainPermissionClass)) {
            $listener = new $mainPermissionClass();
            if ($listener instanceof SimpleCrudListenerInterface) {
                $listener->$action($event);
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen(CrudAfterCreatedEvent::class, [self::class, 'afterCreated']);
        $events->listen(CrudAfterDeleteEvent::class, [self::class, 'afterDelete']);
        $events->listen(CrudAfterSaveEvent::class, [self::class, 'afterSave']);
        $events->listen(CrudAfterUpdatedEvent::class, [self::class, 'afterUpdated']);
        $events->listen(CrudBeforeDeleteEvent::class, [self::class, 'beforeDelete']);
        $events->listen(CrudBeforeRenderFormEvent::class, [self::class, 'beforeRenderForm']);
        $events->listen(CrudBeforeSaveEvent::class, [self::class, 'beforeSave']);
        $events->listen(CrudPreparingQueryBuilderForDatatableEvent::class, [self::class, 'preparingQueryBuilderForDatatable']);
        $events->listen(CrudBeforeSendRowsToDatatableEvent::class, [self::class, 'beforeSendRowsToDatatable']);
        $events->listen(CrudBeforeShowEntityEvent::class, [self::class, 'beforeShowEntity']);
        $events->listen(CrudPermissionEvent::class, [self::class, 'checkPermission']);
        $events->listen(CrudPreparingDatatableEvent::class, [self::class, 'preparingDatatable']);
    }
}
