<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace Endorbit\SimpleCrud\Services;


class SimpleCrudForm extends SimpleCrudEntityPage
{
    public const FIELDS = ['hidden', 'text', 'password', 'email', 'number', 'textarea', 'json', 'checkbox', 'datetime-local', 'date', 'time', 'select', 'multiselect', 'file', 'image', 'texteditor', 'multifile', 'multiimage'];
    public const APPEARS = ['index', 'show', 'update', 'create'];
    protected $formId;
    protected $form = '';


    public function __construct(CrudEvent $crudEvent)
    {
        parent::__construct($crudEvent);
    }

    public function setFormFieldsByConfig(): SimpleCrudForm
    {
        $this->currentEntity = $this->crudEvent->getEntity();

        foreach ($this->crudEvent->getEloquentClass()::getAttributesInfo() as $configs => $label) {
            $fieldSrv = new ConfigInterpreting($configs);
            $fieldName = $fieldSrv->getFieldName();
            $relatFieldName = $fieldSrv->getRelatModelNameField();

            //ha nincsen benne hogy jelenjen meg az aktuális action oldalon akkor szevasz
            if (!in_array($this->crudEvent->getAction(), $fieldSrv->getConfig()) && !in_array('all', $fieldSrv->getConfig())) continue;

            if ($relatFieldName) {
                $select = $this->getSelect2FromEloquentClass($label, $fieldSrv);
                $this->fields[$fieldName] = $select;
                continue;
            }

            $out = $this->getHtmlField($label, $fieldSrv);

            $this->fields[$fieldName] = $out;
        }

        return $this;
    }

    public function setFormDefByConfig(): SimpleCrudForm
    {
        $this->form = '<div id="' . $this->crudEvent->getEloquentClassName() . '-datatableblock">
        <form method="post" action="" class="' . config('simplecrud.html_class.form_tag') . '" enctype="multipart/form-data" >';
        return $this;
    }

    public function setTitleByConfig(): SimpleCrudForm
    {
        if ($this->crudEvent->getEntity()) {
            $nameField = SimpleCrudHelper::getNameFieldOf($this->crudEvent->getEloquentClass());
            $this->title = $this->crudEvent->getEntity()->{$nameField};
        } else {
            $this->title = 'Új elem:  ' . (SimpleCrudHelper::getEloquentClassTitle($this->crudEvent->getEloquentClass()));
        }
        return $this;
    }


    public function setActionsByConfig(): SimpleCrudForm
    {
        $this->actions['submmit'] = '<input type="submit" value="Mentés" class="' . config('simplecrud.html_class.input_submit') . '" >';
        $this->actions['delete'] = '<a class="btn btn-md btn-danger"
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
        $this->actions['index'] = '<a class="btn btn-md btn-default"  href="' . route('simplecrud-index', ['eloquentClass' => $this->crudEvent->getEloquentClassName()]) . '">
                        <span class="glyphicon glyphicon-list"></span>
            Lista
            </a> ';


        if ($this->crudEvent->getId()) {
            $this->actions['show'] = '<a class="btn btn-md btn-default"  href="' . route('simplecrud-show', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '">
                        <span class="glyphicon glyphicon-eye-open"></span>
            Megtekintés
            </a>';
        }

        return $this;
    }

    protected function getHtmlField($label, $fieldSrv)
    {
        /** @var ConfigInterpreting $fieldSrv */
        $type = $fieldSrv->getFormFieldType();
        $fieldName = $fieldSrv->getFieldName();
        $required = $fieldSrv->isRequired() ? 'required' : '';

        $out = '';
        $value = $this->currentEntity ? $this->currentEntity->{$fieldName} : '';

        $out .= '<div title="' . $fieldName . '" class="simplecrud-form-field-container" id="simplecrud-' . $fieldName . '-container">';
        $out .= '<label for="simplecrud-' . $fieldName . '" class="' . config('simplecrud.html_class.label_tag') . '" >' . $label;
        $out .= $type == 'json' ? ' (JSON)' : '';
        $out .= $required ? ' <span style="color:red;">*</span>' : '';
        $out .= '</label>';

        switch ($type) {
            case 'hidden':
            case 'password':
            case 'email':
            case 'time':
            case 'number':
                $out .= '<input type="' . $type . '" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="'
                    . config('simplecrud.html_class.input_tag') . '"  ' . ($type == 'number' ? ' step="0.000001" ' : '') . ' value="'
                    . (old($fieldName) !== null ? old($fieldName) : $value) . '" ' . $required . '><br>';
                break;
            case 'date':
            case 'datetime-local':

                if ($value instanceof \DateTime) {
                    $value->format('Y-m-d' . ($value === 'datetime' ? ' H:i:s' : ''));
                }

                $out .= '<input type="' . ($type === 'datetime' ? 'datetime-local' : $type) . '" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="'
                    . config('simplecrud.html_class.input_tag') . '"  value="'
                    . (old($fieldName) !== null ? old($fieldName) : $value) . '" ' . $required . '><br>';
                break;
            case 'checkbox':
                $out .= '<input type="hidden" id="simplecrud-hidden-' . $fieldName . '" name="' . $fieldName . '" value="0">';
                $out .= '<input type="' . $type . '" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="'
                    . config('simplecrud.html_class.input_tag_checkbox') . '"  value="1" '
                    . ((old($fieldName) !== null ? old($fieldName) : $value) ? ' checked ' : '') . '" ' . $required . '><br>';
                break;
            case 'text':
                if ((config('simplecrud.multilang_all_text') || in_array('multilang', $fieldSrv->getPointlessConfigKey()))) {
                    $langs = config('simplecrud.multilang_languages');
                    if (empty($langs)) $langs = [config('app.locale')];
                    $out .= '<br>';
                    foreach ($langs as $lang) {
                        $out .= '<i>(' . $lang . ')</i><br><input type="' . $type . '" id="simplecrud-' . $fieldName . '-LANG_' . $lang . '" name="' . $fieldName . '-LANG_' . $lang . '" class="'
                            . config('simplecrud.html_class.input_tag') . '"  value="'
                            . (old($fieldName . '-LANG_' . $lang) !== null ? old($fieldName . '-LANG_' . $lang) : SimpleCrudHelper::getTextValueByLang(strval($value), strval($lang), true))
                            . '" ' . $required . '><br>';
                    }
                } else {
                    $out .= '<input type="' . $type . '" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="'
                        . config('simplecrud.html_class.input_tag') . '"  value="'
                        . (old($fieldName) !== null ? old($fieldName) : $value) . '" ' . $required . '><br>';
                }

                break;
            case 'textarea':
                if ((config('simplecrud.multilang_all_text') || in_array('multilang', $fieldSrv->getPointlessConfigKey()))) {
                    $langs = config('simplecrud.multilang_languages');
                    if (empty($langs)) $langs = [config('app.locale')];
                    $out .= '<br>';
                    foreach ($langs as $lang) {
                        $out .= '<i>(' . $lang . ')</i><br><textarea rows="15" id="simplecrud-' . ($fieldName . '-LANG_' . $lang) . '" name="' . ($fieldName . '-LANG_' . $lang) . '" class="' . config('simplecrud.html_class.textarea_tag') . '" ' . $required . '>'
                            . (old($fieldName . '-LANG_' . $lang) !== null ? old($fieldName . '-LANG_' . $lang) : SimpleCrudHelper::getTextValueByLang(strval($value), strval($lang), true)) .
                            '</textarea><br>';
                    }
                } else {
                    $out .= '<textarea rows="15" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="' . config('simplecrud.html_class.textarea_tag') . '" ' . $required . '>' . (old($fieldName) !== null ? old($fieldName) : $value) . '</textarea><br>';

                }
                break;
            case 'texteditor':
                if ((config('simplecrud.multilang_all_text') || in_array('multilang', $fieldSrv->getPointlessConfigKey()))) {
                    $langs = config('simplecrud.multilang_languages');
                    if (empty($langs)) $langs = [config('app.locale')];
                    $out .= '<br>';
                    foreach ($langs as $lang) {
                        $out .= '<i>(' . $lang . ')</i><br><textarea id="simplecrud-' . ($fieldName . '-LANG_' . $lang) . '" name="' . ($fieldName . '-LANG_' . $lang) . '" class="texteditor ' . config('simplecrud.html_class.texteditor') . '" ' . $required . '>'
                            . (old($fieldName . '-LANG_' . $lang) !== null ? old($fieldName . '-LANG_' . $lang) : SimpleCrudHelper::getTextValueByLang(strval($value), strval($lang), true)) .
                            '</textarea><br>';
                    }
                } else {
                    $out .= '<textarea id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="texteditor ' . config('simplecrud.html_class.texteditor') . '" ' . $required . '>'
                        . (old($fieldName) !== null ? old($fieldName) : $value) .
                        '</textarea><br>';

                }
                break;
            case 'json':
                if (is_array($value)) $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $out .= '<textarea rows="15" id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class=" json-field ' . config('simplecrud.html_class.textarea_tag') . '" ' . $required . '>' . (old($fieldName) !== null ? old($fieldName) : $value) . '</textarea>';
                $out .= '<div style="text-align:right" id="json-simplecrud-' . $fieldName . '"></div>';

                $out .= '<br>';
                break;
            case 'file':
            case 'image':
                if ($this->crudEvent->getId()) {
                    $ahref = route('simplecrud-file', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $value;
                    $out .= $value
                        ? ('<div><a href="' . $ahref . '">' . $value . '</a><br><input type="checkbox" name="' . $fieldName . '_file_delete_" class=""> Fájl törlése
                        </div>')
                        : '';
                }

                $required = $required && empty($value) ? $required : '';
                $out .= '<input type="file"  ' . ($type == 'image' ? ' accept="image/*" ' : '') . ' id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="' . config('simplecrud.html_class.input_tag') . '"  value="" ' . $required . '><br>';
                break;
            case 'multifile':
            case 'multiimage':
                if ($this->crudEvent->getId()) {
                    if (is_null($value)) $value = [];
                    foreach ($value as $k => $file) {
                        $ahref = route('simplecrud-file', ['eloquentClass' => $this->crudEvent->getEloquentClassName(), 'id' => $this->crudEvent->getId()]) . '?file=' . $file;
                        $out .= $file
                            ? ('<div><input type="checkbox" name="' . $fieldName . '_file_delete_' . str_replace('.', '_', basename($file)) . '" class=""> Fájl törlése <a href="' . $ahref . '">' . $file . '</a></div>')
                            : '';
                    }


                    $required = $required && empty($value) ? $required : '';
                    $out .= '<input type="file"  ' . ($type == 'image' ? ' accept="image/*" ' : '') . ' id="simplecrud-' . $fieldName . '" name="' . $fieldName . '" class="' . config('simplecrud.html_class.input_tag') . '"  value="" ' . $required . '><br>';
                } else {
                    $out .= '<br><i>CSAK ELSŐ MENTÉS UTÁN, HA MÁR LÉTEZIK ID!</i>';
                }
                break;
        }
        $out .= '</div>';
        return $out;
    }

    protected function getSelect2FromEloquentClass($label, $fieldSrv): string
    {
        $selectEloClass = $fieldSrv->getSelectFieldSourceClass();
        $selectEloClass = explode('\\', $selectEloClass);
        $selectEloClass = array_pop($selectEloClass);
        $relatMethod = $fieldSrv->getFieldName();
        $relatFieldName = $fieldSrv->getRelatModelNameField();
        $isMultiple = ($fieldSrv->getFormFieldType() == 'multiselect');
        $out = '<br>

<input type="hidden" name="' . $relatMethod . '[]" value="">

<label for="simplecrud-' . $relatMethod . '" class="' . config('simplecrud.html_class.label_tag') . '" >' . $label . '</label><br>
<select name="' . $relatMethod . '[]" id="simplecrud-' . $relatMethod . '" ' . ($isMultiple ? 'multiple' : '') . '>';

        $out .= '</select>';
        $ajaxUrl = route('xhrSelect2', ['eloquentClass' => $this->crudEvent->getEloquentClassName()]);
        $limit = config('datatable.max_element_per_ajax_loading'); //erre hivatkozik az XHR is!


        $out .= <<<JSBLOCK
<script>
  $("#simplecrud-{$relatMethod}").select2({
            dropdownAutoWidth: true,
            //containerCssClass:  'form-control width25pc mobile-width100pc',
           // dropdownCssClass: 'form-control width25pc mobile-width100pc',
            allowClear: true,
            placeholder: " ...",
            language: 'hu',
            ajax: {
                url: '{$ajaxUrl}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page || 1,
                        model: '{$selectEloClass}',
                        relatFieldName: '{$relatFieldName}'
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * {$limit}) < data.total_count
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });
</script>
JSBLOCK;


        if ($this->currentEntity) {
            $selected = $this->currentEntity->$relatMethod()->pluck($relatFieldName, $this->currentEntity->getKeyName());
            if ($selected->count()) {
                foreach ($selected as $key => $item) {
                    $out .= '<script>';
                    $out .= <<<JS
            var option = new Option("{$item}", "{$key}", true, true);
            $("#simplecrud-{$relatMethod}").append(option).trigger('change');
            // manually trigger the `select2:select` event
            $("#simplecrud-{$relatMethod}").trigger({
                type: 'select2:select',
                params: {
                     //   data: data
                }
            });
JS;
                    $out .= '</script>';
                }
            }
        }
        $out .= '<br>';
        return $out;
    }

    public function render(): string
    {
        $out = '';
        $out .= $this->form;
        $out .= '<div class="toolbar"><input type="hidden" name="' . ($this->crudEvent->getEntity()?->getKeyName() ?? 'id') . '" value="' . $this->crudEvent->getId() . '">';
        $out .= implode(PHP_EOL, $this->actions);
        $out .= '</div>';

        $out .= '<input type="hidden" name="_token" value="' . \Illuminate\Support\Facades\Request::session()->token() . '" />';

        $out .= implode(PHP_EOL, $this->fields);

        $out .= $this->footer;
        $out .= $this->getJsBlock();

        $out .= '<div class="modal" data-keyboard="true" tabindex="-1" id="Modal-Simplecrud">
                    <div class="modal-dialog"><div class="modal-content"><div class="modal-body">
                                <h4 id="modalTitle" class="modal-title"></h4>
                                <p class="text-center font-normal-regular" id="dialogText"></p>
                                <span id="selected-elem-nr" class="selected-elem-nr text-center"></span></div>
                            <div class="text-center modal-button">
                                <button type="submit" name="submit-delete" id="ok-button" class="btn btn-primary" value=""></button>
                                <button type="button" class="btn btn-default" id="cancel-button" data-dismiss="modal"></button>
                            </div></div></div></div></form></div>';


        return $out;

    }

    protected function getJsBlock()
    {
        $jsb = <<<JSBLOCK
<script>

  function isValidJson(field) {
     var valu = $(field).val();
     if(valu === "") {
        $(field).html("");
        return true;
        }
    var fieldValid = $("#json-" + field.id)
    try {
    //TODO: jQuery 3-nál JSON.parse kell majd használni
        var theJson = jQuery.parseJSON(valu);
        //theJson.mező_neve lehet részletesen is validálni, hogy lehessen kötelező mezőket meghatározni stb.

        fieldValid.html(
            $("<span style=\'color:green\'>Valid Json</span>"
        ));
        return true;
    }
    catch (e) {
        fieldValid.html(
            $("<span style=\'color:red\'>Invalid Json " +e.message + "</span>"
        ));
        return false;
    }
  }

    $(".json-field").on("change", function() {
        isValidJson(this);
    });

    $("input[type=submit]").click(function(e) {
        $(".json-field").each(function() {
           if(!isValidJson(this)) {
          $(this).get(0).setCustomValidity("Invalid Json!");
      } else {
          $(this).get(0).setCustomValidity("");
      }
     })
    });


JSBLOCK;

        $jsb .= config('simplecrud.wysiwyg_in_body');

        $jsb .= '</script>';

        return $jsb;
    }


    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm(string $form)
    {
        $this->form = $form;
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


}
