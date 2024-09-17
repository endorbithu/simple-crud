<?php

namespace Endorbit\SimpleCrud\Models;

use Endorbit\SimpleCrud\Contracts\CrudModelInterface;
use Illuminate\Database\Eloquent\Model;

class SimpleCrudActivityLog extends Model implements CrudModelInterface
{

    protected $guarded = [];

    protected $casts = [
        'diff' => 'array',
    ];

    public static function getAttributesInfo(): array
    {

        return [
            'id' => 'id',
            'created_at|text|show|index' => 'created_at_UTC',
            'admin_id|number|show|index' => 'admin_id',
            'type|text|show|index' => 'type',
            'affected_entity|text|show|index' => 'affected_entity',
            'entity_id|number|show|index' => 'entity_id',
            'diff|json|show' => 'diff',
            'nr_of_affected_rows|number|show|index' => 'nr_of_affected_rows',
            'updated_at|datetime-local|show|index' => 'updated_at',
        ];
    }

}
