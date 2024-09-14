<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:33
 */

namespace DelocalZrt\SimpleCrud\Events;

use DelocalZrt\SimpleCrud\Contracts\FieldCollectionInterface;
use DelocalZrt\SimpleCrud\Services\CrudEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudAfterSaveEvent
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
        $this->crudEvent = $crudEvent;
        $this->data = $data;
    }



    /**
     * @return FieldCollectionInterface
     */
    public function getData(): FieldCollectionInterface
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

    public function getEntity(): Model
    {
        return $this->crudEvent->getEntity();
    }
}
