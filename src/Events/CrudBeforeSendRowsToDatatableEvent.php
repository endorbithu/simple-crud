<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:33
 */

namespace Endorbit\SimpleCrud\Events;

use Endorbit\SimpleCrud\Services\CrudEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CrudBeforeSendRowsToDatatableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  Collection $rows */
    protected $rows;
    /** @var CrudEvent $crudEvent */
    protected $crudEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent, Collection $rows)
    {
        $this->crudEvent = $crudEvent;
        $this->rows = $rows;
    }

    /**
     * @return Collection
     */
    public function getRows(): Collection
    {
        return $this->rows;
    }

    /**
     * @return Collection
     */
    public function setRows(Collection $rows)
    {
        $this->rows = $rows;
    }


    /**
     * @return mixed
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }


}
