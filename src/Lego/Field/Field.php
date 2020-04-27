<?php

namespace JA\Lego\Field;

use JA\Lego\Support\Parser;
use JA\Lego\Foundation\Str as LegoStr;
use JA\Lego\Foundation\Register;
use JA\Lego\Foundation\Query\Query;
use JA\Lego\Foundation\Carrier\Carrier;
use JA\Lego\Widget\Concern\HasMode;
use JA\Lego\Widget\Concern\HasHtmlAttributes;
use JA\Lego\Widget\Concern\Contract\HasMode as HasModeContract;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Mews\Purifier\Facades\Purifier;
use Symfony\Component\ClassLoader\ClassMapGenerator;

abstract class Field extends Carrier implements HasModeContract
{
    use HasMode,
        HasHtmlAttributes;

    const OPERATOR_EQ = '=';
    const OPERATOR_GT = '>';
    const OPERATOR_GTE = '>=';
    const OPERATOR_LT = '<';
    const OPERATOR_LTE = '<=';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_STARTS_WITH = 'starts_with';
    const OPERATOR_ENDS_WITH = 'ends_with';

    const OPERATOR_FUNC_MAP = [
        self::OPERATOR_EQ          => 'whereEquals',
        self::OPERATOR_GT          => 'whereGt',
        self::OPERATOR_GTE         => 'whereGte',
        self::OPERATOR_LT          => 'whereLt',
        self::OPERATOR_LTE         => 'whereLte',
        self::OPERATOR_STARTS_WITH => 'whereStartsWith',
        self::OPERATOR_ENDS_WITH   => 'whereEndsWith',
        self::OPERATOR_CONTAINS    => 'whereContains',
    ];

    protected $name;
    protected $label;

    protected $elementType;
    protected $elementName;
    protected $elementNamePrefix;

    protected $scope;
    protected $queryOperator = self::OPERATOR_EQ;

    protected $relationPath;
    protected $column;
    protected $jsonPath;

    protected $defaultValue;
    protected $originalValue;
    protected $requestValue;
    protected $displayValue;

    protected $validators = [];
    protected $discardValidators = [];

    protected $enableSave = true;
    protected $enablePurifier = true;

    protected $purifierConfig;

    protected static $registeredFields = [];

    public function __construct($data, $name, $label = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->elementName = Parser::flattenAttributePath($name);

        list($this->relationPath, $this->column, $this->jsonPath) = Parser::splitAttributePath($name);

        $this->purifierConfig = $this->getFieldConfig('purifier') ?: config('ja-lego.fields.purifier');

        parent::__construct($data);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return is_null($this->label) ? ucwords(str_replace(['.', ':'], ' ', $this->getName())) : $this->label;
    }

    public function getElementId()
    {
        return 'ja-lego-' . $this->getElementName();
    }

    public function getElementType()
    {
        return $this->elementType;
    }

    public function getElementName()
    {
        return $this->elementNamePrefix . $this->elementName;
    }

    public function getElementNamePrefix()
    {
        return $this->elementNamePrefix;
    }

    public function setElementNamePrefix($prefix)
    {
        $this->elementNamePrefix = $prefix;

        return $this;
    }

    public function getPlaceholder($default = null)
    {
        return $this->getAttributeString('placeholder', $default);
    }

    public function placeholder($placeholder = null)
    {
        $this->setAttribute('placeholder', $placeholder);

        return $this;
    }

    public function note($note = null)
    {
        $this->getMessages()->add('note', $note);

        return $this;
    }

    public function getRelationPath()
    {
        return $this->relationPath;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getJsonPath()
    {
        return $this->jsonPath;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    public function setOriginalValue($value)
    {
        $this->originalValue = $value;

        return $this;
    }

    public function getRequestValue()
    {
        return $this->requestValue;
    }

    public function setRequestValue($value)
    {
        if ($this->enablePurifier && $value && is_string($value)) {
            $value = Purifier::clean($value, $this->purifierConfig);
        }

        $this->requestValue = $value;

        return $this;
    }

    public function validRequestValue()
    {
        $value = $this->getRequestValue();

        if (is_null($value) || $value === false) {
            return false;
        }

        if (is_string($value)) {
            return !LegoStr::isEmpty($value);
        }

        return true;
    }

    public function getDisplayValue()
    {
        return $this->displayValue;
    }

    public function setDisplayValue($value)
    {
        $this->displayValue = $value;

        return $this;
    }

    public function required($condition = true)
    {
        return $this->addValidator('required', $condition);
    }

    public function getValidators()
    {
        return $this->validators;
    }

    public function getExplodedValidators()
    {
        $userValidators = [];
        $systemValidators = [];
        foreach ($this->validators as $validator)
        {
            if ($validator instanceof \Closure) {
                $userValidators[] = $validator;
            } else {
                $systemValidators[] = $validator;
            }
        }

        return [$systemValidators, $userValidators];
    }

    public function getSystemValidators()
    {
        list($systemValidators, $_) = $this->getExplodedValidators();

        return $systemValidators;
    }

    public function getUserValidators()
    {
        list($_, $userValidators) = $this->getExplodedValidators();

        return $userValidators;
    }

    public function addValidator($validator, $condition = true)
    {
        if (!value($condition)) {
            return $this;
        }

        if ($validator instanceof \Closure) {
            $this->validators[] = $validator;
        } else {
            if (!Str::contains($validator, 'regex') && Str::contains($validator, '|')) {
                foreach (explode('|', $validator) as $item) {
                    $this->addValidator($item);
                }
            } elseif (!in_array($validator, $this->validators) && !in_array($validator, $this->discardValidators)) {
                $this->validators[] = $validator;
            }
        }

        return $this;
    }

    public function validate($data = [])
    {
        if ($this->isReadonlyMode()) {
            return true;
        }

        $registedValidators = Register::get(Register::TYPE_FIELD_VALIDATOR) ?? [];
        if ($registedValidators) {
            foreach ($registedValidators as $registedValidator) {
                call_user_func_array($registedValidator, [$this, $this->data]);
            }
        }

        list($systemValidators, $userValidators) = $this->getExplodedValidators();

        $value = $this->getRequestValue();
        $validator = Validator::make(
            $data ?: [$this->getElementName() => $value],
            [$this->getElementName() => $systemValidators],
            [],
            [$this->getElementName() => $this->getLabel()]
        );

        if ($validator->fails()) {
            $this->getErrors()->merge($validator->messages());

            return false;
        }

        foreach ($userValidators as $closure) {
            $error = call_user_func($closure, $value);
            if (is_string($error)) {
                $this->getErrors()->add('error', $error);

                return false;
            }
        }

        return true;
    }

    public function enableSave()
    {
        $this->enableSave = true;

        return $this;
    }

    public function disableSave()
    {
        $this->enableSave = false;

        return $this;
    }

    public function enablePurifier()
    {
        $this->enablePurifier = true;

        return $this;
    }

    public function disablePurifier()
    {
        $this->enablePurifier = false;

        return $this;
    }

    public function configurePurifier($config)
    {
        $this->purifierConfig = is_array($config) ? array_merge($this->purifierConfig, $config) : $config;

        return $this;
    }

    public function scope($scope)
    {
        if ($scope && (is_string($scope) || $scope instanceof \Closure)) {
            $this->scope = $scope ?: $this->getName();
        }

        return $this;
    }

    public function applyScope(Query $query)
    {
        if (is_string($this->scope)) {
            $query->whereScope($this->scope, $this->getRequestValue());
        } else {
            call_user_func_array($this->scope, [$query, $this->getRequestValue()]);
        }

        return $query;
    }

    public function applyQuery(Query $query)
    {
        if ($method = static::OPERATOR_FUNC_MAP[$this->queryOperator] ?? null) {
            return call_user_func_array([$query, $method], [$this->getName(), $this->getRequestValue()]);
        }

        return $this;
    }

    public function filter(Query $query)
    {
        if ($this->scope) {
            $this->applyScope($query);
        } else {
            $this->applyQuery($query);
        }
    }

    public function setQueryOperator($operator)
    {
        $this->queryOperator = $operator;

        return $this;
    }

    public function whereEquals()
    {
        return $this->setQueryOperator(self::OPERATOR_EQ);
    }

    public function whereGt()
    {
        return $this->setQueryOperator(self::OPERATOR_GT);
    }

    public function whereGte()
    {
        return $this->setQueryOperator(self::OPERATOR_GTE);
    }

    public function whereLt()
    {
        return $this->setQueryOperator(self::OPERATOR_LT);
    }

    public function whereLte()
    {
        return $this->setQueryOperator(self::OPERATOR_LTE);
    }

    public function whereContains()
    {
        return $this->setQueryOperator(self::OPERATOR_CONTAINS);
    }

    public function whereStartsWith()
    {
        return $this->setQueryOperator(self::OPERATOR_STARTS_WITH);
    }

    public function whereEndsWith()
    {
        return $this->setQueryOperator(self::OPERATOR_ENDS_WITH);
    }

    protected function getFieldConfig($key, $default = null)
    {
        return config('ja-lego.fields.fields.' . static::class . '.' . $key, $default);
    }

    public static function registerFields()
    {
        if (!static::$registeredFields) {
            $fields = ClassMapGenerator::createMap(__DIR__ . '/Fields');
            foreach ($fields as $field => $path) {
                static::$registeredFields[class_basename($field)] = $field;
            }

            foreach (config('ja-lego.fields.defined') as $field) {
                static::$registeredFields[class_basename($field)] = $field;
            }
        }

        return static::$registeredFields;
    }

    protected function view($view, $data = [])
    {
        return view($view, $data)->with('field', $this);
    }

    public function renderReadonly()
    {
        // TODO: Implement renderReadonly() method.
    }

    public function renderEditable()
    {
        // TODO: Implement renderEditable() method.
    }

    public function renderDisabled()
    {
        // TODO: Implement renderDisabled() method.
    }

    public function process()
    {
        $this->setAttribute([
            'id'   => $this->getElementId(),
            'name' => $this->getElementName(),

            'ja-lego-type' => 'Field',
            'ja-lego-field-type' => class_basename(static::class),
            'ja-lego-field-mode' => $this->mode,
        ]);

        $this->setAttribute(config('ja-lego.fields.attributes', []));
    }
}
