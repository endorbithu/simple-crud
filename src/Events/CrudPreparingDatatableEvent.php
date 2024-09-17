<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:33
 */

namespace Endorbit\SimpleCrud\Events;

use Endorbit\Datatable\Contracts\DatatableConfigInterface;
use Endorbit\Datatable\Contracts\DatatableServiceInterface;
use Endorbit\SimpleCrud\Services\CrudEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudPreparingDatatableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  DatatableConfigInterface $datatable */
    protected $datatable;
    /** @var CrudEvent $crudEvent */
    protected $crudEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent, DatatableServiceInterface $datatable)
    {
        $this->crudEvent = $crudEvent;
        $this->datatable = $datatable;
    }

    /**
     * @return DatatableConfigInterface
     */
    public function getDatatable(): DatatableConfigInterface
    {
        return $this->datatable;
    }

    /**
     * @return CrudEvent
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }


}
