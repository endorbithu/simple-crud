<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:21
 */

namespace DelocalZrt\SimpleCrud\Contracts;


interface FieldCollectionInterface
{
    public function getField($fieldName);

    public function setField(string $fieldName, $value);

    public function setFields(array $fields);

    public function getFields(): array;

    public function removeField(string $fieldName);


}
