<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace Endorbit\SimpleCrud\Services;


use Carbon\Carbon;
use Endorbit\SimpleCrud\Contracts\CrudModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimpleCrudShow extends SimpleCrudEntityPage
{

    public function __construct(CrudEvent $crudEvent)
    {
        parent::__construct($crudEvent);
    }

    public function setActionsByConfig(): SimpleCrudShow
    {
        $this->actions['update'] = '

            <a class="btn btn-md btn-default"  href="' . route('simplecrud-update', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '">
                        <span class="glyphicon glyphicon-pencil"></span>
            Módosítás
            </a>';
        $this->actions['delete'] = '
                <a class="btn btn-md btn-danger"
                           id="action-999"
                           data-title="Törlés"
                           data-table-id-name="' . $this->crudEvent->getEloquentClassName() . '"
                           data-url="' . route('simplecrud-delete', ['eloquentClass' => $this->crudEvent->getEloquentClassName()]) . '"
                           data-warning-text="Biztos?"
                           data-count-elem=""
                           data-ok-button-label="Törlés"
                           data-ok-button-value="Törlés"
                           data-cancel-button-label="Mégse"
                           data-keyboard="true"
                           data-toggle="modal"
                           data-target="#Modal-Simplecrud"
                           href="#Modal-Simplecrud"
               ><span class="glyphicon glyphicon-remove"></span>
                Törlés
                </a>';

        $this->actions['index'] = '
        <a class="btn btn-md btn-default"  href="' . route('simplecrud-index', ['eloquentClass' => $this->crudEvent->getEloquentClassName()]) . '">
                        <span class="glyphicon glyphicon-list"></span>
            Lista
            </a>';

        return $this;
    }

    public function setFieldsByConfig(): SimpleCrudShow
    {

        foreach ($this->crudEvent->getEloquentClass()::getAttributesInfo() as $configs => $label) {
            $fieldSrv = new ConfigInterpreting($configs);
            $fieldName = $fieldSrv->getFieldName();
            $relatFieldName = $fieldSrv->getRelatModelNameField();

            //ha nincsen benne hogy jelenjen meg az aktuális action oldalon akkor szevasz
            if (!in_array($this->crudEvent->getAction(), $fieldSrv->getPointlessConfigKey())
                && !in_array('all', $fieldSrv->getPointlessConfigKey())
            ) continue;

            $row = $this->crudEvent->getEntity();

            $data = '<div class="' . config('simplecrud.html_class.div_show_field_container') . '">';
            $data .= '<div title="' . $fieldName . '" class="' . config('simplecrud.html_class.div_show_field_name') . '">' . $label . '</div>';

            $data .= '<div class="' . config('simplecrud.html_class.div_show_value') . '">';
            if (key_exists($fieldName, $row->getAttributes())) {
                if (in_array('date', $fieldSrv->getPointlessConfigKey()) && !empty($row->{$fieldName})) {
                    $val = ($row->{$fieldName} instanceof \DateTime)
                        ? $row->{$fieldName}->format('Y-m-d')
                        : Carbon::parse($row->{$fieldName})->format('Y-m-d');
                } elseif ($row->{$fieldName} instanceof \DateTime) {
                    $val = $row->{$fieldName}->format('Y-m-d H:i:s');
                } elseif (in_array('checkbox', $fieldSrv->getPointlessConfigKey())) {
                    $val = $row->{$fieldName} ? 'IGEN' : 'NEM';
                } elseif (
                    in_array('texteditor', $fieldSrv->getPointlessConfigKey())
                    || in_array('textarea', $fieldSrv->getPointlessConfigKey())
                    || in_array('text', $fieldSrv->getPointlessConfigKey())
                ) {
                    if ((config('simplecrud.multilang_all_text') || in_array('multilang', $fieldSrv->getPointlessConfigKey()))) {
                        $val = '';
                        $langs = config('simplecrud.multilang_languages');
                        if (empty($langs)) $langs = [config('app.locale')];
                        foreach ($langs as $lang) {
                            $val .= '<br> <i>(' . $lang . ')</i><br>' . SimpleCrudHelper::getTextValueByLang(strval($row->{$fieldName}), $lang, true);
                        }
                        $val = ltrim($val, '<br>');

                    } else {
                        $val = $row->{$fieldName};
                    }

                } elseif (in_array('number', $fieldSrv->getPointlessConfigKey())) {
                    $val = (is_null($row->{$fieldName}) ?: ((floor($row->{$fieldName}) != $row->{$fieldName}) ? $row->{$fieldName} : number_format($row->{$fieldName}, '0', ',', ' ')));
                } else {
                    if ($fieldSrv->getFilePath() && $row->{$fieldName} && in_array('image', $fieldSrv->getPointlessConfigKey())) {
                        $imageSrc = $row->{$fieldName};
                        if (!empty($fieldSrv->getThumbImageDimensions())) {
                            $imageBaseName = basename($row->{$fieldName});
                            $imageSrc = str_replace($imageBaseName, ('thumb_' . $imageBaseName), $imageSrc);
                        }

                        $ahref = route('simplecrud-image', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $row->{$fieldName};
                        $imgSrc = route('simplecrud-image', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $imageSrc;
                        $val = ('<div class="' . config('simplecrud.html_class.image_container_in_show') . '"><a href="' . $ahref . '" target="_blank"><img alt="" src="' . $imgSrc . '" class="' . config('simplecrud.html_class.image_in_show') . '"> </a></div>');
                    } elseif ($fieldSrv->getFilePath() && $row->{$fieldName} && in_array('file', $fieldSrv->getPointlessConfigKey())) {
                        $ahref = route('simplecrud-file', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $row->{$fieldName};
                        $val = ('<div><a href="' . $ahref . '">' . $row->{$fieldName} . '</a></div>');
                    } elseif ($fieldSrv->getFilePath() && $row->{$fieldName} && in_array('multiimage', $fieldSrv->getPointlessConfigKey())) {
                        $val = '<div class="' . config('simplecrud.html_class.image_container_in_show') . '">';
                        $images = is_null($row->{$fieldName}) ? [] : $row->{$fieldName};
                        foreach ($images as $fil) {
                            $fileName = basename($fil);
                            $filThumb = str_replace($fileName, ('thumb_' . $fileName), $fil);
                            $ahref = route('simplecrud-image', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $fil;
                            $imgSrc = route('simplecrud-image', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $filThumb;
                            $val .= ('<a href="' . $ahref . '" target="_blank"><img alt="" src="' . $imgSrc . '" class="' . config('simplecrud.html_class.image_in_show') . '"> </a> ');
                        }
                        $val .= '</div>';
                    } elseif ($fieldSrv->getFilePath() && $row->{$fieldName} && in_array('multifile', $fieldSrv->getPointlessConfigKey())) {
                        $val = '<div>';
                        $files = is_null($row->{$fieldName}) ? [] : $row->{$fieldName};

                        foreach ($files as $fil) {
                            $ahref = route('simplecrud-file', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $fil;
                            $val .= ('<a href="' . $ahref . '">' . $fil . '</a><br>');
                        }
                        $val .= '</div>';

                    } else {
                        $val = $row->{$fieldName};
                        if (is_array($val)) {
                            $val = '<pre>' . json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</pre>';
                        } else {
                            $val = (strip_tags(trim(strval($val))) !== trim(strval($val))) ? ('<pre>' . htmlspecialchars($val, true) . '</pre>') : $val;
                        }
                    }
                }
            } elseif (
                SimpleCrudHelper::isPublicMethodOf($row, $fieldName) && $row->{$fieldName}() instanceof BelongsTo
                || SimpleCrudHelper::isPublicMethodOf($row, $fieldName) && $row->{$fieldName}() instanceof HasOne
            ) {
                $field2 = $row->{$fieldName}()->first();
                if ($field2 instanceof CrudModelInterface) {
                    $objName = (new \ReflectionClass($field2))->getShortName();
                    $url = route('simplecrud-show', ['eloquentClass' => $objName, 'id' => $field2->getKey()]);
                    $val = '<a href="' . $url . '">' . $field2->{$relatFieldName} . '</a>';
                } else {
                    $val = $field2 ? $field2->{$relatFieldName} : '';
                }
            } elseif (
                SimpleCrudHelper::isPublicMethodOf($row, $fieldName) && $row->{$fieldName}() instanceof BelongsToMany
                || SimpleCrudHelper::isPublicMethodOf($row, $fieldName) && $row->{$fieldName}() instanceof HasMany
            ) {
                /** @var  BelongsToMany $a */
                $relatModel = $row->{$fieldName}()->getModel();
                /** @var Collection $val */
                $val = $row->{$fieldName}()->pluck($relatFieldName, $relatModel->getKeyName());

                if ($relatModel instanceof CrudModelInterface) {
                    $objName = (new \ReflectionClass($relatModel))->getShortName();
                    $val->transform(function ($val, $key) use ($objName) {
                        $url = route('simplecrud-show', ['eloquentClass' => $objName, 'id' => $key]);
                        return '<a href="' . $url . '">' . $val . '</a>';
                    });
                }

                $val = $val->implode('<br>');
            } elseif (SimpleCrudHelper::isPublicMethodOf($row, $fieldName) && $row->{$fieldName}() instanceof Model) {
                $m = $row->{$fieldName}();
                if ($m instanceof CrudModelInterface) {
                    $objName = (new \ReflectionClass($m))->getShortName();
                    $url = route('simplecrud-show', ['eloquentClass' => $objName, 'id' => $field2->getKey()]);
                    $val = '<a href="' . $url . '">' . $m->{$relatFieldName} . '</a>';
                } else {
                    $val = $m->{$relatFieldName};
                }

            } elseif (SimpleCrudHelper::isPublicMethodOf($row, ('get' . ucfirst(Str::camel($fieldName)) . 'Attribute'))) {
                $meth = ('get' . Str::camel($fieldName) . 'Attribute');
                if (is_array($row->$meth())) {
                    $val = implode('<br>', $row->{$meth}());
                } elseif ($row->$meth() instanceof \Illuminate\Support\Collection) {
                    $val = $row->$meth()->implode('<br>');
                } else {
                    $val = (strip_tags($row->$meth()) !== $row->$meth()) ? ('<pre>' . htmlspecialchars($row->$meth(), true) . '</pre>') : $row->$meth();
                }
            } else {
                $val = '';
            }

            if (is_array($val)) {
                $data .= '<pre>' . json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
            } else {

                $data .= (is_null($val) ? '-' : (($val === '') ? '<br>' : (strpos($val, '<pre>' !== false)
                    ? $val
                    : (in_array('texteditor', $fieldSrv->getPointlessConfigKey()) ? $val : nl2br($val))
                )));
            }

            $data .= '</div>';
            $data .= '</div>';
            $this->fields[$fieldName] = $data;
        }
        return $this;
    }


    public function render(): string
    {
        $out = '';

        $out .= '<div id="' . $this->crudEvent->getEloquentClassName() . '-datatableblock">
        <form method="post" action="">
        <input type="hidden" name="_token" value="' . \Illuminate\Support\Facades\Request::session()->token() . '" />
        <input type="hidden" name="id" value="' . $this->crudEvent->getId() . '">
        <div class="toolbar">';
        $out .= implode(PHP_EOL, $this->actions);
        $out .= '</div>';

        $out .= implode(PHP_EOL, $this->fields);
        $out .= '<div class="modal" data-keyboard="true" tabindex="-1" id="Modal-Simplecrud">
                    <div class="modal-dialog"><div class="modal-content"><div class="modal-body">
                                <h4 id="modalTitle" class="modal-title">Törlés</h4>
                                <p class="text-center font-normal-regular" id="dialogText"></p>
                                <span id="selected-elem-nr" class="selected-elem-nr text-center"></span></div>
                            <div class="text-center modal-button">
                                <button type="submit" name="submit-delete" id="ok-button" class="btn btn-primary" value=""></button>
                                <button type="button" class="btn btn-default" id="cancel-button" data-dismiss="modal"></button>
                            </div></div></div></div>';
        $out .= '</form></div>';

        $out .= $this->footer;

        return $out;
    }


    public function addAction(string $name, array $action)
    {
        if (isset($action['action'])) {

            $this->actions[$name] = '<a class="btn btn-md btn-default"
                           id="action-999"
                           data-title="' . $action['name'] . '"
                           data-table-id-name="' . $this->crudEvent->getEloquentClassName() . '"
                           data-url="' . $action['action'] . '"
                           data-warning-text="' . ($action['warning'] ?? '') . '"
                           data-count-elem=""
                           data-ok-button-label="Igen"
                           data-ok-button-value="Mégse"
                           data-cancel-button-label="Mégse"
                           data-keyboard="true"
                           data-toggle="modal"
                           data-target="#Modal-Simplecrud"
                           href="#Modal-Simplecrud"
               ><span class="glyphicon glyphicon-' . ($action['icon'] ?? 'flash') . '"></span>
                ' . $action['name'] . '
                </a>';

        } elseif (isset($action['href'])) {
            $this->actions[$name] = '<a class="btn btn-md btn-default"
                        href="' . $action['href'] . '">
                        <span class="glyphicon glyphicon-' . ($action['icon'] ?? 'flash') . '"></span>
            ' . $action['name'] . '
            </a> ';
        }

    }

    public function setTitleByConfig(): SimpleCrudShow
    {
        if ($this->crudEvent->getEntity()) {
            $nameField = SimpleCrudHelper::getNameFieldOf($this->crudEvent->getEloquentClass());
            $this->title = $this->crudEvent->getEntity()->{$nameField};
        }
        return $this;
    }


}
