<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:33
 */

namespace Endorbit\SimpleCrud\Events;

use Endorbit\SimpleCrud\Contracts\FieldCollectionInterface;
use Endorbit\SimpleCrud\Services\CrudEvent;
use Endorbit\SimpleCrud\Services\SavingRequestCollection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudBeforeSaveEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  CrudEvent $crudEvent */
    protected $crudEvent;

    /** @var FieldCollectionInterface $data */
    protected $data;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent, FieldCollectionInterface $data)
    {
        $this->data = $data;
        $this->crudEvent = $crudEvent;
    }


    /**
     * @return CrudEvent
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }

    /**
     * @return FieldCollectionInterface
     */
    public function getData(): SavingRequestCollection
    {
        return $this->data;
    }

    public function getEntity(): ?Model
    {
        return $this->crudEvent->getEntity();
    }

}
