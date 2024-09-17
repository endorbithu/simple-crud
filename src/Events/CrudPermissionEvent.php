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

class CrudPermissionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var CrudEvent $crudEvent */
    protected $crudEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent)
    {
        $this->crudEvent = $crudEvent;
    }

    /**
     * @return CrudEvent
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }


}
