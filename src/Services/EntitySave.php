<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 19.
 * Time: 10:15
 */

namespace Endorbit\SimpleCrud\Services;


use Endorbit\SimpleCrud\Exceptions\UserCanSeeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Illuminate\Support\Facades\File;


class EntitySave
{

    protected $crudEvent;

    /** @var  Model $entity */
    protected $entity;

    /** @var  SavingRequestCollection $requestCollection */
    protected $requestCollection;

    /** @var  Request $request */
    protected $request;

    protected $files = [];
    protected $json = [];
    protected $images = [];
    protected $multiimages = [];
    protected $multifiles = [];

    /**
     * EntitySave constructor.
     * @param CrudEvent $crudEvent
     */
    public function __construct(CrudEvent $crudEvent)
    {
        $this->crudEvent = $crudEvent;
    }


    public function setEntity()
    {
        if ($this->crudEvent->getId()) {
            $this->entity = $this->crudEvent->getEloquentClass()::findOrFail($this->crudEvent->getId());
        } else {
            $model = $this->crudEvent->getEloquentClass();
            $this->entity = new $model();
        }
    }

    public function setFileFields()
    {
        $attrs = $this->entity::getAttributesInfo();
        foreach ($attrs as $conf => $attr) {
            $attrSrv = new ConfigInterpreting($conf);
            if ($attrSrv->getFormFieldType() == 'file') {
                $this->files[$attrSrv->getFieldName()] = $attrSrv;
            } elseif ($attrSrv->getFormFieldType() == 'image') {
                $this->images[$attrSrv->getFieldName()] = $attrSrv;
            } elseif ($attrSrv->getFormFieldType() == 'multifile') {
                $this->multifiles[$attrSrv->getFieldName()] = $attrSrv;
            } elseif ($attrSrv->getFormFieldType() == 'multiimage') {
                $this->multiimages[$attrSrv->getFieldName()] = $attrSrv;
            }
        }
    }

    public function setJsonFields()
    {
        $attrs = $this->entity::getAttributesInfo();
        foreach ($attrs as $conf => $attr) {
            $attrSrv = new ConfigInterpreting($conf);
            if ($attrSrv->getFormFieldType() == 'json') {
                $this->json[$attrSrv->getFieldName()] = $attrSrv;
            }
        }
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setRequestCollection(SavingRequestCollection $requestCollection)
    {
        $this->requestCollection = $requestCollection;
    }

    public function saveEntityDbFieldsAndFile()
    {
        $requestArray = $this->requestCollection->getFields();
        $uploadedFiles = [];
        foreach ($requestArray as $field => $value) {
            //if (!is_numeric($value) && empty($value)) continue;
            $fieldLangExpl = explode('-LANG_', $field);
            $field = $fieldLangExpl[0];

            if (Schema::hasColumn($this->entity->getTable(), $field)) {

                if (is_array($value)) {
                    if (key_exists(0, $value) && !is_numeric($value[0]) && empty($value[0])) {
                        //ez az üres field[] a select előtt hiddenben, hogyha semmi nem jön, akkor elkapja a field nevet,
                        // és ilyenkor töröljünk, tehát üres tömb miatt nem fut le az associative, csak a disassociate
                        unset($value[0]);
                    }
                }

                //file-e
                $file = $this->request->file($field);
                if ($file && (isset($this->files[$field]) || isset($this->multifiles[$field]))) {
                    $fileField = isset($this->files[$field]) ? $this->files[$field] : $this->multifiles[$field];
                    $fileName = $fileField->isKeepFilename()
                        ? str_replace(' ', '_', Str::ascii($file->getClientOriginalName()))
                        : substr(md5(uniqid($file->getClientOriginalName())), -32) . '.' . $file->getClientOriginalExtension();

                    $patExl = explode('/', $fileField->getFilePath());
                    if ($patExl[0] == 'app') unset($patExl[0]);
                    $path = implode('/', $patExl);
                    $path = isset($this->multifiles[$field]) ? (rtrim($path, '/') . '/' . $this->entity->getKey()) : $path;

                    $value = $this->request->file($field)->storeAs($path, $fileName);
                    $uploadedFiles[] = storage_path() . '/' . $value;

                    $value = (strpos($value, 'app/') === 0 ? '' : 'app/') . $value;

                    if (isset($this->multifiles[$field])) {
                        if ($this->entity) {
                            $valueJson = $this->entity->{$field};
                            if (is_null($valueJson)) $valueJson = [];
                            $valueJson[] = $value;
                            $value = $valueJson;
                        } else {
                            $value = [$value];
                        }
                    }

                } elseif ($file && (isset($this->images[$field])) || isset($this->multiimages[$field])) {
                    $image = isset($this->images[$field]) ? $this->images[$field] : $this->multiimages[$field];

                    try {
                        $img = \Intervention\Image\Facades\Image::make($file->path());
                        if (!empty($image->getThumbImageDimensions())) {
                            $imgThumb = \Intervention\Image\Facades\Image::make($file->path());
                        }
                    } catch (NotReadableException $e) {
                        throw new UserCanSeeException($e->getMessage());
                    }
                    $filePath = isset($this->multiimages[$field])
                        ? (rtrim($image->getFilePath(), '/') . '/' . $this->entity->getKey())
                        : $image->getFilePath();

                    if (!file_exists(storage_path($filePath))) {
                        File::makeDirectory(storage_path($filePath), 0755, true);
                        chmod(storage_path($filePath), 0755);
                    }

                    $fileName = $image->isKeepFilename()
                        ? str_replace(' ', '_', Str::ascii($file->getClientOriginalName()))
                        : substr(md5(uniqid($file->getClientOriginalName())), -32) . '.' . $file->getClientOriginalExtension();

                    $tosave = storage_path($filePath) . '/' . $fileName;

                    if (!empty($image->getImageDimensions())) {
                        $img->widen($image->getImageDimensions()[0], function ($const) {
                            $const->aspectRatio();
                        });
                    }

                    $img->save($tosave);

                    if (!empty($image->getThumbImageDimensions())) {
                        //szélesség fix
                        $imgThumb->widen($image->getThumbImageDimensions()[0], function ($const) {
                            $const->aspectRatio();
                        });
                        $toSaveThumb = storage_path($filePath) . '/' . 'thumb_' . $fileName;
                        $imgThumb->save($toSaveThumb);
                    }

                    $value = ($filePath . '/' . $fileName);
                    $uploadedFiles[] = storage_path() . '/' . $value;
                    $value = (strpos($value, 'app/') === 0 ? '' : 'app/') . $value;

                    if (isset($this->multiimages[$field])) {
                        if ($this->entity) {
                            $valueJson = $this->entity->{$field};
                            if (is_null($valueJson)) $valueJson = [];
                            $valueJson[] = $value;
                            $value = $valueJson;
                        } else {
                            $value = [$value];
                        }
                    }

                } elseif (isset($this->json[$field]) && !is_array($value)) {
                    $value = stripcslashes($value);
                    $valueDecode = json_decode($value, true);
                    $value = (is_null($valueDecode)) ? $value : $valueDecode;
                }

                //ha létezik $fieldLangExpl[1], akkor több mező lett beküldve nyelvenként => ennék a mezőnél multilang van
                //itt annyiszor rakjuk össze ugyanúgy a sok nyelvet egy mezővé, ahány nyelv van, tehát végrzzük ugynazt a műveletet
                if (isset($fieldLangExpl[1])) {
                    $langs = config('simplecrud.multilang_languages');
                    if (empty($langs)) $langs = [config('app.locale')];
                    $value = '';

                    //ha csak a főnyelv van kitöltve, akkor ne tegyünk bele <!-- LANG_  jelölőt,
                    // mert nincs szükség rá + bekavarhat lefele nem lesz kompatibilis
                    $onlyMainLang = 0;
                    foreach ($langs as $lang) {
                        if (isset($requestArray[$field . ('-LANG_' . $lang)]) && !empty($requestArray[$field . ('-LANG_' . $lang)])) {
                            $onlyMainLang++;
                        }
                    }

                    foreach ($langs as $lang) {
                        if (isset($requestArray[$field . ('-LANG_' . $lang)])) {
                            if ($onlyMainLang === 1) {
                                $value = $requestArray[$field . ('-LANG_' . $lang)];
                                break;
                            }

                            $value .= ('<!-- LANG_' . $lang . '_LANG -->' . $requestArray[$field . ('-LANG_' . $lang)]);
                        }
                    }

                }

                $this->entity->{$field} = $value;

            } elseif (strpos($field, '_file_delete_') > 0) { //mivel a formban a fájlfeltöltés előtt van, így ez fog előbb lefutni a key az valid lesz
                $fileFieldExp = explode('_file_delete_', $field);

                $fileField = $fileFieldExp[0];

                if (!isset($fileFieldExp[1]) || !$fileFieldExp[1]) {
                    $fi = str_replace('../', '', $this->entity->{$fileField});
                    $fiBase = basename($fi);
                    $thumbFi = str_replace($fiBase, ('thumb_' . $fiBase), $fi);

                    if (File::exists(storage_path($fi))) {
                        File::delete(storage_path($fi));
                        if (File::exists(storage_path($thumbFi))) {
                            File::delete(storage_path($thumbFi));
                        }
                    } elseif (strpos($this->entity->{$fileField}, 'app/') !== 0 && File::exists(storage_path('app/' . $fi))) {
                        File::delete(storage_path('app/' . $fi));
                        if (File::exists(storage_path('app/' . $thumbFi))) {
                            File::delete(storage_path('app/' . $thumbFi));
                        }
                    }
                    $this->entity->{$fileField} = null;
                    $requestArray[$fileField] = null;
                } else {
                    //multifiles
                    $deletableFile = $fileFieldExp[1];
                    $jsonFiles = $this->entity->{$fileField};
                    foreach ($jsonFiles as $k => $f) {
                        $fBase = basename($f);
                        $thumbF = str_replace($fBase, ('thumb_' . $fBase), $f);
                        if (str_replace('.', '_', $fBase) != str_replace('.', '_', $deletableFile)) continue;
                        unset($jsonFiles[$k]);
                        $f = str_replace('../', '', $f);
                        if (File::exists(storage_path($f))) {
                            File::delete(storage_path($f));
                            if (File::exists(storage_path($thumbF))) {
                                File::delete(storage_path($thumbF));
                            }
                        } elseif (strpos($f, 'app/') !== 0 && File::exists(storage_path('app/' . $f))) {
                            File::delete(storage_path('app/' . $f));
                            if (File::exists(storage_path('app/' . $thumbF))) {
                                File::delete(storage_path('app/' . $thumbF));
                            }
                        }
                    }
                    $this->entity->{$fileField} = $jsonFiles;
                    $requestArray[$fileField] = $jsonFiles;
                }

            } elseif (SimpleCrudHelper::isPublicMethodOf($this->entity, $field) && $this->entity->{$field}() instanceof BelongsTo) {
                $valBT = is_array($value) ? array_pop($value) : $value;
                if (!is_numeric($valBT) && empty($valBT)) {
                    $this->entity->{$field}()->disassociate();
                } elseif (is_numeric($valBT)) {
                    $this->entity->{$field}()->associate($valBT);
                }

            }
        }

        try {
            $diff = ($this->entity->getDirty());
            $this->entity->save();

            return $diff;
        } catch (\Throwable $e) {
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $uploadedFile) {
                    if (File::exists($uploadedFile)) File::delete($uploadedFile);
                }
            }
            throw $e;
        }
    }


    public function saveRelationsAndDynamicFields()
    {
        $orig = [];
        $diff = [];
        //itt meg azokat a mezőket kell beállítani, amikhez kell az adott entity ID-je (Many to many) + dinamikus mezők
        foreach ($this->request->post() as $field => $value) {

            if ((SimpleCrudHelper::isPublicMethodOf($this->entity, ('set' . ucfirst(Str::camel($field)) . 'Attribute')))) {
                if (!is_numeric($value) && empty($value)) $this->entity->{$field} = null;
            } elseif (
                SimpleCrudHelper::isPublicMethodOf($this->entity, $field) && $this->entity->{$field}() instanceof BelongsToMany
            ) {
                $orig[$field] = $this->entity->{$field}()->allRelatedIds()->toArray();
                $this->entity->{$field}()->detach();

                if (is_array($value)) {
                    if (key_exists(0, $value) && !is_numeric($value[0]) && empty($value[0])) {
                        //ez az üres field[] a select előtt hiddenben, hogyha semmi nem jön, akkor elkapja a field nevet,
                        // és ilyenkor töröljünk mindent, tehát üres tömb miatt nem fut le az attach, csak a detach fentebb
                        unset($value[0]);
                    }
                    foreach ($value as $kk => $item) {
                        //elméletileg lehet eloquent model is és akkor sem is numeric ugye
                        if (is_string($item) && !is_numeric($item)) unset($value[$kk]);
                    }
                } elseif (is_string($value) && !is_numeric($value)) {
                    $value = [];
                }

                if (!empty($value)) $this->entity->{$field}()->attach($value);
                $diff[$field] = $this->entity->{$field}()->allRelatedIds($value)->toArray();

            }
        }

        foreach ($orig as $f => $ids) {
            $ids = array_unique($ids);
            sort($ids);
            $diff[$f] = array_unique($diff[$f]);
            sort($diff[$f]);

            if (($ids) == ($diff[$f])) {
                unset($diff[$f]);
            }
        }

        $this->entity->save();

        return $diff;
    }

    public function setEntityForCrudEvent()
    {
        $this->crudEvent->setEntity($this->entity);
    }


}
