<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:20
 */

namespace DelocalZrt\SimpleCrud\Services;


use Illuminate\Database\Eloquent\Model;

class ConfigInterpreting
{

    protected $config;

    /** @var string $fieldName */
    protected $fieldName;

    /** @var string $pluckFieldName */
    protected $pluckFieldName;

    /** @var string $filePath */
    protected $filePath;

    /** @var array $imageDimensions */
    protected $imageDimensions;

    /** @var array $thumbImageDimensions */
    protected $thumbImageDimensions;

    /** @var Model $selectFieldSourceClass */
    protected $selectFieldSourceClass;

    /** @var array $pointlessConfigKey */
    protected $pointlessConfigKey = [];

    protected $formFieldType = '';

    protected $required = false;
    protected $keepFilename;

    public function __construct(string $configKey)
    {
        $this->config = explode('|', $configKey);
        $this->setFieldName();
        $this->setSelectFieldSourceClass();
        $this->setPointlessConfigKey();
        $this->setFormFieldType();
        $this->setFilePath();
        $this->setImageDimensions();
        $this->setRequired();


    }


    protected function setFieldName(): void
    {
        $fieldData = explode('.', $this->config[0]);
        $this->fieldName = $fieldData[0];
        $this->pluckFieldName = ($fieldData[1] ?? null);
        unset($this->config[0]);
    }


    protected function setFilePath(): void
    {
        if (!in_array('file', $this->config) && !in_array('image', $this->config)
            && !in_array('multifile', $this->config) && !in_array('multiimage', $this->config)) return;

        foreach ($this->config as $item) {
            if (strpos($item, '/') === false) continue;

            $path = trim($item, '/');
            $this->filePath = ($path);
        }

        if (!$this->filePath) {
            $this->filePath = ('app/simplecrud');
        }

    }


    protected function setImageDimensions(): void
    {
        if (!in_array('image', $this->config) && !in_array('multiimage', $this->config)) return;

        foreach ($this->config as $item) {
            if ((!preg_match('/^[0-9]+x[0-9]+$/', $item)) &&
                (!preg_match('/^[0-9]+X[0-9]+$/', $item)) &&
                (!preg_match('/^[0-9]+\*[0-9]+$/', $item))
            ) continue;

            $deli = (strpos($item, 'x') !== false) ? 'x' : (strpos($item, 'X') !== false ? 'X' : '*');
            $this->imageDimensions = explode($deli, $item);
        }

        foreach ($this->config as $item) {
            if ((!preg_match('/^thumb[0-9]+x[0-9]+$/', $item)) &&
                (!preg_match('/^thumb[0-9]+X[0-9]+$/', $item)) &&
                (!preg_match('/^thumb[0-9]+\*[0-9]+$/', $item))
            ) {
                continue;
            }

            $item = str_replace('thumb', '', $item);

            $deli = (strpos($item, 'x') !== false) ? 'x' : (strpos($item, 'X') !== false ? 'X' : '*');
            $this->thumbImageDimensions = explode($deli, $item);
        }


    }


    protected function setSelectFieldSourceClass(): void
    {
        foreach ($this->config as $item) {
            if (strpos($item, 'select') === false && strpos($item, 'multiselect') === false) continue;

            $exp = explode('.', $item);
            if (!isset($exp[1]))
                throw new \Exception($this->fieldName . ': No source Eloquent class for select e.g.: (multi)select.AnEloquentClass ');

            $this->selectFieldSourceClass = SimpleCrudHelper::getFullSimpleCrudClassFromClassBasename($exp[1]);
        }
    }


    protected function setPointlessConfigKey(): void
    {
        foreach ($this->config as $item) {
            if (strpos($item, '.') === false) {
                $this->pointlessConfigKey[] = $item;
                continue;
            }

            $exp = explode('.', $item);
            $this->pointlessConfigKey[] = $exp[0];
        }
    }

    /**
     * @return string
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getRelatModelNameField(): ?string
    {
        return $this->pluckFieldName;
    }

    /**
     * @return string
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @return array
     */
    public function getImageDimensions(): ?array
    {
        return $this->imageDimensions;
    }

    /**
     * @return array
     */
    public function getThumbImageDimensions(): ?array
    {
        return $this->thumbImageDimensions;
    }

    /**
     * @return Model
     */
    public function getSelectFieldSourceClass(): ?string
    {
        return $this->selectFieldSourceClass;
    }

    /**
     * @return array
     */
    public function getPointlessConfigKey(): array
    {
        return $this->pointlessConfigKey;
    }

    /**
     * @return string[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }


    public function setFormFieldType(): void
    {
        foreach (SimpleCrudForm::FIELDS as $FIELD) {
            if (in_array($FIELD, $this->pointlessConfigKey)) $this->formFieldType = $FIELD;
        }
    }


    public function getFormFieldType(): string
    {
        return $this->formFieldType;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(): void
    {
        $this->required = in_array('required', $this->pointlessConfigKey);
    }

    /**
     * @return bool
     */
    public function isKeepFilename(): bool
    {
        if (!is_bool($this->keepFilename)) {
            $this->keepFilename = in_array('keepfilename', $this->pointlessConfigKey);
        }

        return $this->keepFilename;
    }


}
