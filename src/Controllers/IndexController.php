<?php

namespace DelocalZrt\SimpleCrud\Controllers;

use DelocalZrt\Datatable\Services\Datatable;
use DelocalZrt\SimpleCrud\Contracts\CrudModelInterface;
use DelocalZrt\SimpleCrud\Events\CrudAfterCreatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterUpdatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeRenderFormEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSendRowsToDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeShowEntityEvent;
use DelocalZrt\SimpleCrud\Events\CrudPermissionEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingQueryBuilderForDatatableEvent;
use DelocalZrt\SimpleCrud\Exceptions\SimpleCrudPermissionDeniedException;
use DelocalZrt\SimpleCrud\Exceptions\UserCanSeeException;
use DelocalZrt\SimpleCrud\Models\SimpleCrudActivityLog;
use DelocalZrt\SimpleCrud\Services\ConfigInterpreting;
use DelocalZrt\SimpleCrud\Services\CrudEvent;
use DelocalZrt\SimpleCrud\Services\EntitySave;
use DelocalZrt\SimpleCrud\Services\SavingRequestCollection;
use DelocalZrt\SimpleCrud\Services\SimpleCrudForm;
use DelocalZrt\SimpleCrud\Services\SimpleCrudHelper;
use DelocalZrt\SimpleCrud\Services\SimpleCrudShow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;


class IndexController extends BaseController
{
    /** @var string CrudModelInterface */
    protected $eloquentClass;
    /** @var CrudEvent $crudEvent */
    protected $crudEvent;
    protected $eloquentClassName;
    protected $errorMsg = 'Hiba lépett fel a művelet során!';
    protected $permissionError = 'Nincs jogosultság az adott művelethez';

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }

        //\DelocalZrt\Datatable\Jobs\ProcessBigCsvExportToFile kell, hogy meg tudja hívni
        $request = App::make(Request::class);

        //TODO FIXME, itt kivonjuk minden middleware alól az xhr kéréseket, mert nincs megoldva (az IP ellenőrzésen kívül), hogy hitelesítse a requestet
        if (stripos($request->url(), 'xhr') === false) $this->middleware(config('simplecrud.middleware', []));

        try {
            $this->eloquentClass = SimpleCrudHelper::getFullSimpleCrudClassFromClassBasename($request->eloquentClass);

            $this->crudEvent = new CrudEvent();
            $this->eloquentClassName = $request->eloquentClass;
            $this->crudEvent->setEloquentClass($this->eloquentClass);
            $this->crudEvent->setEloquentClassName($request->eloquentClass);

            if ($request->id > 0) {
                $this->crudEvent->setId($request->id);
                $mod = $this->eloquentClass::find($request->id);
                if (empty($mod)) {
                    throw new UserCanSeeException('A keresett tartalom nem található ID:' . $request->id);
                }
                $this->crudEvent->setEntity($mod);
            }

            if ($request->eloquentClass && !is_subclass_of($this->eloquentClass, CrudModelInterface::class)) {
                throw new \Exception($request->eloquentClass . ' has not implemented:' . CrudModelInterface::class . ' OR this namespace has not added to simplecrud config');
            }

        } catch (HttpException $e) {
            throw $e;
        } catch (UserCanSeeException $e) {
            $error = ($e->getMessage());
        } catch (\Throwable $e) {
            if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                throw $e;
            } else {
                $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                $this->errorMsg .= ' Hiba azonosító: ' . $errorId;
                $error = $this->errorMsg;
            }
        }

        if (isset($error)) {
            die($error); //view-t nem lehet innen generálni
        }

    }

    public function index(Request $request)
    {
        $action = 'index';
        $datatable = '';
        $description = '';
        $customHeader = SimpleCrudHelper::getCustomHtmlHeadInner($action);
        $customBefore = SimpleCrudHelper::getCustomHtmlBeforeContent($action);
        $customAfter = SimpleCrudHelper::getCustomHtmlAfterContent($action);
        $title = SimpleCrudHelper::getEloquentClassTitle($this->eloquentClass);
        $this->crudEvent->setAction($action);

        try {
            CrudPermissionEvent::dispatch($this->crudEvent);

            $attrs = $this->eloquentClass::getAttributesInfo();
            $ent = new $this->eloquentClass;

            foreach ($attrs as $k => $label) {
                $ops = new ConfigInterpreting($k);
                if ($ops->getFieldName() !== $ent->getKeyName() && !in_array('index', $ops->getConfig()) && !in_array('all', $ops->getConfig())) {
                    unset($attrs[$k]);
                }
            }

            //itt már kiderült, hogy a $request->eloquentClass az valid (construct)
            $datatable = Datatable::create(
                $request->eloquentClass,
                'checkbox',
                route('simplecrudIndexXhr', ['eloquentClass' => $request->eloquentClass])
                . (!empty($request->query()) ? ('?' . (http_build_query($request->query()))) : ''),
                $attrs,
                $this->eloquentClass
            );

            $datatable->addAction(
                [
                    'href' => route('simplecrud-create', ['eloquentClass' => $this->eloquentClassName]),
                    'name' => 'Új',
                    'warning' => 'Biztos?',
                    'icon' => 'plus',
                ], 'new'
            );

            $datatable->addAction(
                [
                    'action' => route('simplecrud-delete', ['eloquentClass' => $this->eloquentClassName]),
                    'name' => 'Törlés',
                    'warning' => 'Biztos?',
                    'icon' => 'remove',
                ], 'delete'
            );


            CrudPreparingDatatableEvent::dispatch($this->crudEvent, $datatable);
            $description = $datatable->getDescription();

            $csvFiles = [];
            foreach ($datatable->getCsvFiles() as $getCsvFile) {
                $csvFiles[route('simplecrud-file', ['eloquentClass' => $this->eloquentClassName, 'id' => 0]) . '?file=' . $getCsvFile] = $getCsvFile;
            }

        } catch (HttpException $e) {
            throw $e;
        } catch (SimpleCrudPermissionDeniedException $e) {
            $error = $this->errorMsg = ($e->getMessage() ? $e->getMessage() : $this->permissionError);
        } catch (UserCanSeeException $e) {
            $error = $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                throw $e;
            } else {
                $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                $error = $this->errorMsg .= ' Hiba azonosító: ' . $errorId;
            }
        }

        return view('simplecrud::index')->with([
            'customHeader' => $customHeader,
            'customBefore' => $customBefore,
            'error' => $error ?? null,
            'title' => $title,
            'description' => $description,
            'datatable' => $datatable,
            'csvfiles' => $csvFiles,
            'customAfter' => $customAfter,
        ]);

    }

    public function indexXhr(Request $request)
    {
        //Ha jobbol (console-ból) jön, akkor a constructot az első sorban returnöljük, mert különben
        //a route cache folyamatnál elszáll a request huánya miatt
        if (!$this->crudEvent) {
            $this->eloquentClass = SimpleCrudHelper::getFullSimpleCrudClassFromClassBasename($request->eloquentClass);

            $this->crudEvent = new CrudEvent();
            $this->eloquentClassName = $request->eloquentClass;
            $this->crudEvent->setEloquentClass($this->eloquentClass);
            $this->crudEvent->setEloquentClassName($request->eloquentClass);
        }

        $this->crudEvent->setAction('index-xhr');
        CrudPermissionEvent::dispatch($this->crudEvent);

        $this->eloquentClass = $this->eloquentClass ?? $request->eloquentClass; //csv background save-nél ezt kell nézni
        $ent = new $this->eloquentClass;
        $modelForQueryBuilder = $this->eloquentClass::query();
        $multilangFields = [];
        //N+1 hibát elkerülendő with -eljük a mezőt (ami a relat metódus neve is az eloquent modelben)
        foreach ($this->eloquentClass::getAttributesInfo() as $k => $attr) {
            $ops = new ConfigInterpreting($k);
            if ($ops->getFieldName() !== $ent->getKeyName() && !in_array('index', $ops->getConfig()) && !in_array('all', $ops->getConfig())) {
                continue;
            }
            if ($ops->getRelatModelNameField()) {
                $modelForQueryBuilder->with($ops->getFieldName());
            }

            if (
                ($ops->getFormFieldType() == 'text' || $ops->getFormFieldType() == 'textarea' || $ops->getFormFieldType() == 'texteditor')
                &&
                (config('simplecrud.multilang_all_text') || in_array('multilang', $ops->getPointlessConfigKey()))
            ) {
                $multilangFields[] = $ops->getFieldName();
            }
        }

        $requestAll = $request->all();
        $requestAll['eloquentClass'] = $this->eloquentClassName;

        if (Datatable::hasTriggeredBackgroundCsvGenerating(self::class, __FUNCTION__, $modelForQueryBuilder, $requestAll)) {
            return back()->with('success', 'A csv fájl generálása folyamatban');
        }

        CrudPreparingQueryBuilderForDatatableEvent::dispatch($this->crudEvent, $modelForQueryBuilder);
        $data = Datatable::getFilteredRows($modelForQueryBuilder, $request->all());

        $nameField = SimpleCrudHelper::getNameFieldOf($this->eloquentClass);

        foreach ($data['data'] as &$row) {
            foreach ($row as $field => $val) {
                if (in_array($field, $multilangFields)) {
                    $row[$field] = SimpleCrudHelper::getTextValueByLang($row[$field]);
                }
            }

            $row[$nameField] = '<a href="' . route('simplecrud-show', ['eloquentClass' => $this->eloquentClassName, 'id' => $row[$ent->getKeyName()]]) . '">'
                . ($row[$nameField] ? $row[$nameField] : '<i>[id:' . $row[$ent->getKeyName()] . ']</i>') . '</a> &nbsp;&nbsp;'
                . '<a href="' . route('simplecrud-update', ['eloquentClass' => $this->eloquentClassName, 'id' => $row[$ent->getKeyName()]]) . '" '
                . 'class="btn btn-sm btn-default">'
                . '<span class="glyphicon glyphicon-pencil">
</span></a>';
        }

        $rows = collect($data['data']);

        CrudBeforeSendRowsToDatatableEvent::dispatch($this->crudEvent, $rows);

        $data['data'] = $rows->toArray();

        return (Datatable::getResponseForDatatable($data, $this->crudEvent->getEloquentClass()));
    }


    public function show(Request $request)
    {
        $action = 'show';
        $this->crudEvent->setAction($action);
        $content = '';
        $title = '';
        $description = '';

        $customHeader = SimpleCrudHelper::getCustomHtmlHeadInner($action);
        $customBefore = SimpleCrudHelper::getCustomHtmlBeforeContent($action);
        $customAfter = SimpleCrudHelper::getCustomHtmlAfterContent($action);

        try {
            CrudPermissionEvent::dispatch($this->crudEvent);

            $srv = new SimpleCrudShow($this->crudEvent);
            $srv
                ->setTitleByConfig()
                ->setActionsByConfig()
                ->setFieldsByConfig();

            $description = $srv->getDescription();
            $title = $srv->getTitle();

            CrudBeforeShowEntityEvent::dispatch($this->crudEvent, $srv);
            $content = $srv->render();

        } catch (HttpException $e) {
            throw $e;
            $error = $this->errorMsg = ($e->getMessage() ? $e->getMessage() : $this->permissionError);
        } catch (UserCanSeeException $e) {
            $error = $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                throw $e;
            } else {
                $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                $error = $this->errorMsg .= ' Hiba azonosító: ' . $errorId;
            }
        }

        return view('simplecrud::index')->with([
            'customHeader' => $customHeader,
            'customBefore' => $customBefore,
            'error' => $error ?? null,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'customAfter' => $customAfter,
        ]);

    }


    protected function updateOrCreate($action)
    {
        $this->crudEvent->setAction($action);
        $content = '';
        $customHeader = SimpleCrudHelper::getCustomHtmlHeadInner($action);
        $customBefore = SimpleCrudHelper::getCustomHtmlBeforeContent($action);
        $customAfter = SimpleCrudHelper::getCustomHtmlAfterContent($action);
        $title = '';
        $description = '';
        try {
            CrudPermissionEvent::dispatch($this->crudEvent);

            $this->crudEvent->setAction($action);
            $srv = new SimpleCrudForm($this->crudEvent);
            $srv->setTitleByConfig()
                ->setFormDefByConfig()
                ->setFormFieldsByConfig()
                ->setActionsByConfig();

            CrudBeforeRenderFormEvent::dispatch($this->crudEvent, $srv);

            $title = $srv->getTitle();
            $description = $srv->getDescription();
            $content = $srv->render();

        } catch (HttpException $e) {
            throw $e;
        } catch (SimpleCrudPermissionDeniedException $e) {
            $error = $this->errorMsg = ($e->getMessage() ? $e->getMessage() : $this->permissionError);
        } catch (UserCanSeeException $e) {
            $error = $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                throw $e;
            } else {
                $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                $error = $this->errorMsg .= ' Hiba azonosító: ' . $errorId;
            }
        }

        return view('simplecrud::index')->with([
            'customHeader' => $customHeader,
            'customBefore' => $customBefore,
            'error' => $error ?? null,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'customAfter' => $customAfter,
        ]);
    }


    protected function save(Request $request, $action)
    {
        $this->crudEvent->setAction($action);
        try {
            CrudPermissionEvent::dispatch($this->crudEvent);
            $srv = new EntitySave($this->crudEvent);
            $srv->setEntity();
            $srv->setFileFields();
            $srv->setJsonFields();

            $requestCollection = new SavingRequestCollection($this->crudEvent, $request);

            CrudBeforeSaveEvent::dispatch($this->crudEvent, $requestCollection);

            $srv->setRequest($request);
            $srv->setRequestCollection($requestCollection);

            $diffColumns = $srv->saveEntityDbFieldsAndFile();
            $diffRelat = $srv->saveRelationsAndDynamicFields();

            $srv->setEntityForCrudEvent();

            CrudAfterSaveEvent::dispatch($this->crudEvent, $requestCollection);


            if ($this->crudEvent->getAction() == 'create') {
                CrudAfterCreatedEvent::dispatch($this->crudEvent, $requestCollection);
            } elseif ($this->crudEvent->getAction() == 'update') {
                CrudAfterUpdatedEvent::dispatch($this->crudEvent, $requestCollection);
            }

            try {
                $diff = array_merge_recursive($diffColumns, $diffRelat);

                $activity = new SimpleCrudActivityLog();
                $activity->admin_id = auth(config('simplecrud.admin_guard', 'admin'))->id() ?? 0; //ha valami akadás miatt nem találja
                $activity->type = $this->crudEvent->getAction();
                $activity->affected_entity = $this->crudEvent->getEntity()->getTable();
                $activity->entity_id = $this->crudEvent->getEntity()->getKey();
                $activity->diff = $diff;
                $activity->nr_of_affected_rows = 1;
                $activity->created_at = now();
                $activity->updated_at = now();
                $activity->save();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SimpleCrud activity log error: ' . PHP_EOL . $e->__toString());
            }


        } catch (HttpException $e) {
            throw $e;
        } catch (SimpleCrudPermissionDeniedException $e) {
            $error = $this->errorMsg = ($e->getMessage() ? $e->getMessage() : $this->permissionError);
        } catch (UserCanSeeException $e) {
            $error = $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Duplicate entry')) {
                $errorExpl = explode('Duplicate entry', $e->getMessage());
                $errorExpl = explode('for key', $errorExpl[1]);
                $error = 'Ilyen értékkel: ' . $errorExpl[0] . ' már van rekord az adatbázisban!';
            } else {
                if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                    throw $e;
                } else {
                    $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                    $error = ($this->errorMsg .= ' Hiba azonosító: ' . $errorId);
                }
            }
        }

        if (isset($error)) {
            return back()->with(['error' => $error])->withInput();
        }

        return Redirect::route('simplecrud-show', ['eloquentClass' => $this->eloquentClassName, 'id' => $this->crudEvent->getEntity()->getKey()])
            ->with(['success' => 'Sikeres mentés!']);
    }

    public function delete(Request $request)
    {
        $counter = 0;
        try {
            $this->crudEvent->setAction('delete');
            CrudPermissionEvent::dispatch($this->crudEvent);

            if ($request->post('id')) {
                $this->crudEvent->setAction('delete');
                $this->crudEvent->setId($request->post('id'));
                $ids = [$request->post('id')];
            } else {
                //Datatable:: küldi az ID-ket
                $ids = Datatable::getFilteredIds($this->crudEvent->getEloquentClass(), $request->all());
            }
            $anId = null;
            $countIds = count($ids);
            foreach ($ids as $id) {
                $entity = $this->crudEvent->getEloquentClass()::find($id);
                if (!$entity) continue;
                if ($countIds == 1) {
                    $anId = $id;
                }

                $this->crudEvent->setEntity($entity);
                $this->crudEvent->setId($entity->getKey());


                CrudBeforeDeleteEvent::dispatch($this->crudEvent);
                foreach ($entity::getAttributesInfo() as $configKey => $field) {
                    $con = new ConfigInterpreting($configKey);
                    if ($con->getFilePath()) {
                        $field = $con->getFieldName();
                        if ($entity->{$field} && is_string($entity->{$field}) && file_exists(storage_path($entity->{$field})) && !is_dir(storage_path($entity->{$field}))) {
                            FILE::delete(storage_path($entity->{$field}));
                        } elseif ($entity->{$field} && is_array($entity->{$field})) {
                            foreach ($entity->{$field} as $ff) {
                                $basename = basename($ff);
                                $dir = str_replace($basename, '', $ff);

                                if (file_exists(storage_path($ff)) && !is_dir(storage_path($ff))) {
                                    FILE::delete(storage_path($ff));
                                }

                                if (file_exists(storage_path($dir . 'thumb_' . $basename))) {
                                    FILE::delete(storage_path($dir . 'thumb_' . $basename));
                                }

                                if (FILE::exists($dir) && FILE::isEmptyDirectory($dir)) {
                                    FILE::deleteDirectory($dir);
                                }
                            }


                        }
                    }
                }
                $entity->delete();

                CrudAfterDeleteEvent::dispatch($this->crudEvent);
                $counter++;
            }

            try {
                $elClass = $this->crudEvent->getEloquentClass();
                $activity = new SimpleCrudActivityLog();
                $activity->admin_id = auth(config('simplecrud.admin_guard', 'admin'))->id() ?? 0; //ha valami akadás miatt nem találja
                $activity->type = 'delete';
                $activity->affected_entity = (new $elClass)->getTable();
                $activity->entity_id = $anId;
                $activity->diff = null;
                $activity->nr_of_affected_rows = $countIds;
                $activity->created_at = now();
                $activity->updated_at = now();
                $activity->save();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SimpleCrud activity log error: ' . PHP_EOL . $e->__toString());
            }


        } catch (HttpException $e) {
            throw $e;
        } catch (SimpleCrudPermissionDeniedException $e) {
            $error = $this->errorMsg = ($e->getMessage() ? $e->getMessage() : $this->permissionError);
        } catch (UserCanSeeException $e) {
            $error = $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            if (config('app.debug') || !config('simplecrud.own_error_handling')) {
                throw $e;
            } else {
                $errorId = SimpleCrudHelper::logError($this->crudEvent, $e);
                $error = ($this->errorMsg .= ' Hiba azonosító: ' . $errorId);
            }
        }

        return Redirect::route('simplecrud-index', ['eloquentClass' => $request->eloquentClass])
            ->with(['success' => 'Sikeres törlés! (' . $counter . ' db)',
                'simplecrudError' => ($error ?? null)]);
    }


    public function update(Request $request)
    {
        return $this->updateOrCreate('update');
    }

    public function create(Request $request)
    {
        return $this->updateOrCreate('create');
    }


    public function updatePost(Request $request)
    {
        return $this->save($request, 'update');
    }


    public function createPost(Request $request)
    {
        return $this->save($request, 'create');
    }

    public function getFileByPath(Request $request)
    {
        CrudPermissionEvent::dispatch($this->crudEvent);

        $file = $request->file;
        $file = str_replace('../', '', $file); //path traversal protect
        if (!$file) return [];

        if (file_exists(storage_path($file))) {
            return response()->download(storage_path($file));
        } elseif (strpos($file, 'app/') !== 0 && file_exists(storage_path('app/' . $file))) {
            return response()->download(storage_path('app/' . $file));
        }
        die('File not found!');
    }

    public function showImageByPath(Request $request)
    {
        CrudPermissionEvent::dispatch($this->crudEvent);

        $path = null;
        $file = $request->file;
        $file = str_replace('../', '', $file); //path traversal protect

        if (file_exists(storage_path($file))) {
            $path = storage_path($file);
        } elseif (strpos($file, 'app/') !== 0 && file_exists(storage_path('app/' . $file))) {
            $path = storage_path('app/' . $file);
        }

        if ($path) {
            return response()->file($path);
        }

        die('File not found!');
    }


}

