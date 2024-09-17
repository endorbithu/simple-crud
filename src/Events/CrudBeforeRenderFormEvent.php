<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:33
 */

namespace Endorbit\SimpleCrud\Events;

use Endorbit\SimpleCrud\Contracts\SimpleCrudEntityPageInterface;
use Endorbit\SimpleCrud\Services\CrudEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudBeforeRenderFormEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var SimpleCrudEntityPageInterface $data */
    protected $data;

    /** @var CrudEvent $crudEvent */
    protected $crudEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent, SimpleCrudEntityPageInterface $data)
    {
        $this->crudEvent = $crudEvent;
        $this->data = $data;
    }

    /**
     * @return SimpleCrudEntityPageInterface
     */
    public function getData(): SimpleCrudEntityPageInterface
    {
        return $this->data;
    }

    /**
     * @return CrudEvent
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }

    public function getEntity(): ?Model
    {
        return $this->crudEvent->getEntity();
    }


}
