<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace Endorbit\SimpleCrud\Services;


use Endorbit\SimpleCrud\Contracts\CrudModelInterface;
use Endorbit\SimpleCrud\Contracts\SimpleCrudListenerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SimpleCrudHelper
{
    public static function genereateErrorCode($crudEvent): string
    {
        return ('E.' . intval(auth()->id()) . '.' . ($crudEvent->getAction() ?? 'constr') . '.' . date('Ymd_His_' . substr((string) microtime(true), -4)));

    }

    public static function logError(CrudEvent $crudEvent, \Throwable $e): string
    {
        $errorId = self::genereateErrorCode($crudEvent);
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/simplecrud.log'),
        ])->error($errorId . PHP_EOL . $e);

        return $errorId;
    }

    public static function getNameFieldOf(string $eloquentClass): string
    {
        $attrs = $eloquentClass::getAttributesInfo();

        $i = 0;
        foreach ($attrs as $config => $attr) {
            if ($i == 1) {
                return (new ConfigInterpreting($config))->getFieldName();
            }
            $i++;
        }
    }

    public static function getCustomHtmlHeadInner(string $action): array
    {
        return ($head = config('simplecrud.blades.' . $action . '.in_head_tag')) ? $head : config('simplecrud.blades.in_head_tag');
    }

    public static function getCustomHtmlBeforeContent(string $action): array
    {
        return ($before = config('simplecrud.blades.' . $action . '.before_content')) ? $before : config('simplecrud.blades.before_content');
    }

    public static function getCustomHtmlAfterContent(string $action): array
    {
        return ($after = config('simplecrud.blades.' . $action . '.after_content')) ? $after : config('simplecrud.blades.after_content');
    }

    public static function getSimpleCrudableAppModelClasses(): array
    {
        $paths = [];
        $models = [];

        if (is_dir(app_path() . "/Models")) {
            $paths['App'] = app_path() . "/Models";
        }

        $namespaces = array_unique(array_merge_recursive(['Endorbit\SimpleCrud'], config('simplecrud.namespaces')));

        foreach ($namespaces as $ns) {
            if ($ns === 'App') {
                continue;
            }

            if (strpos($ns, '/') !== false && file_exists(base_path($ns))) {
                foreach (File::directories(base_path($ns)) as $dir) {
                    $p = base_path(rtrim($ns, '/') . '/' . basename($dir) . '/src/Models');
                    if (file_exists($p)) {
                        $paths[basename($dir)] = $p;
                    }
                }
            }
        }

        foreach ($paths as $ns => $path) {
            $results = scandir($path);

            foreach ($results as $result) {
                if ($result === '.' or $result === '..') continue;
                $filename = $path . '/' . $result;

                if (is_dir($filename)) continue;

                $modelClassName = substr($result, 0, -4);
                $modelClass = $ns . '\\Models\\' . $modelClassName;

                if (!(is_subclass_of($modelClass, Model::class))) continue;
                $modelObj = new $modelClass();

                if ($modelObj instanceof CrudModelInterface) {
                    $models[$modelClassName] = $modelClass;
                }
            }
        }

        return $models;
    }

    public static function getAppModelsWithUrl(): array
    {
        $out = [];
        foreach (self::getSimpleCrudableAppModelClasses() as $className => $class) {
            $url = rtrim(config('app.url'), '/') . '/simplecrud/' . $className;

            $name = $class::$title ?? str($className)->headline();
            $out[$url] = $name;
        }
        asort($out);
        return $out;
    }

    /**
     * @param string $eloquentClass
     * @return string
     */
    public static function getEloquentClassTitle(string $eloquentClass): string
    {
        $eloquentClassName = class_basename($eloquentClass);

        return ($eloquentClass::$title ?? $eloquentClassName);
    }

    public static function isPublicMethodOf(object $obj, $methodName): bool
    {
        if (!method_exists($obj, $methodName)) return false;

        $publicMeths = (new \ReflectionClass($obj))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMeths as $met) {
            if ($met->name === $methodName) return true;
        }

        return false;
    }

    public static function getTextValueByLang(string $value, string $lang = '', bool $force = false): string
    {
        $configLangs = config('simplecrud.multilang_languages');
        $defaultLang = !empty($configLangs) && is_array($configLangs) ? $configLangs[0] : (config('app.locale') ?? 'en');
        $langs = [];

        if (mb_strpos($value, ('<!-- LANG_')) !== 0) {
            $value = '<!-- LANG_' . $defaultLang . '_LANG -->' . $value; //ha nem nyelv elválasztóval kezdődött, akkor a defaultot beírja elsőnek, és akkor meglesz
        }

        $langExpl = explode('LANG_', $value);
        foreach ($langExpl as $i => $aLang) {
            if ($i === 0) continue;
            $langSignExp = explode('_LANG', $aLang);
            $langs[] = $langSignExp[0];
        }

        $values = preg_split('/\<\!\-\- LANG_[a-z]+_LANG \-\-\>/', $value);

        if (count($values) > count($langs)) {
            array_unshift($langs, $defaultLang);
        }

        if (!in_array($lang, $langs)) {
            $lang = $defaultLang;
            if ($force) {
                return '';
            }
        }

        $langs = array_flip($langs);
        return trim($values[$langs[$lang]]);
    }

    public static function getFullSimpleCrudClassFromClassBasename(string $classBasename, string $midDir = 'Models')
    {
        $namespaces = array_unique(array_merge_recursive(['Endorbit\SimpleCrud'], config('simplecrud.namespaces')));

        foreach ($namespaces as $ns) {

            if (strpos($ns, '/') !== false && file_exists(base_path($ns))) {
                foreach (File::directories(base_path($ns)) as $dir) {
                    $nsSub = basename($dir);
                    $classFull = '\\' . $nsSub . '\\' . $midDir . '\\' . $classBasename;
                    if (class_exists($classFull) && (is_subclass_of($classFull, CrudModelInterface::class) || is_subclass_of($classFull, SimpleCrudListenerInterface::class))) {
                        return $classFull;
                    }
                }
                continue;
            }

            $classFull = '\\' . $ns . '\\' . $midDir . '\\' . $classBasename;
            if (class_exists($classFull)) {
                return $classFull;
            }
        }

        return null;
    }

}
