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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudPreparingQueryBuilderForDatatableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  Builder $queryBuilder */
    protected $queryBuilder;
    /** @var CrudEvent $crudEvent */
    protected $crudEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CrudEvent $crudEvent, Builder $queryBuilder)
    {
        $this->crudEvent = $crudEvent;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }


    /**
     * @return mixed
     */
    public function getCrudEvent(): CrudEvent
    {
        return $this->crudEvent;
    }


}
