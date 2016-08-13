<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Ilch;

use Ilch\Registry;

use Ilch\Validation\Data;
use Ilch\Validation\Error;
use Ilch\Validation\ErrorBag;

/**
 * Validation class
 */
class Validation
{

    protected static $builtInValidators = [
        'required'  => '\Ilch\Validation\Validators\Required',      // Parameters: none
        'same'      => '\Ilch\Validation\Validators\Same',          // Parameters: strict:0|1, as:field_name
        'captcha'   => '\Ilch\Validation\Validators\Captcha',       // Parameters: none
        'length'    => '\Ilch\Validation\Validators\Length',        // Parameters: min: int, max: int
        'url'       => '\Ilch\Validation\Validators\Url',           // Parameters: none
        'email'     => '\Ilch\Validation\Validators\Email',         // Parameters: none
    ];

    protected static $validators = [];
    protected static $customFieldAliases = [];

    protected $input;
    protected $rules;
    protected $breakChain;

    protected $errors;

    protected $errorBag;
    protected $translator;

    protected $fields_with_error = [];
    protected $passes = true;

    /**
     * Constructor
     *
     * @param Array $input An array with input
     * @param Array $rules An array with validation rules
     */
    private function __construct($input, $rules, $breakChain, $autoRun)
    {
        $this->input = $input;
        $this->rules = $rules;
        $this->breakChain = $breakChain;
        $this->errorBag = new Errorbag();
        $this->translator = Registry::get('translator');

        if ($autoRun) {
            $this->run();
        }
    }

    /**
     * Runs the validation
     */
    public function run()
    {
        $availableValidators = self::getValidators();

        foreach ($this->rules as $field => $rules) {
            // Iterating over the rules
            foreach (explode("|", $rules) as $rule) {
                // Iterating over the rules of that field
                if (strpos($rule, ",") === false) {
                    $vRule = $rule;
                    $vData = new Data($field, array_dot($this->input, $field), [], $this->input);
                } else {
                    $params = explode(",", $rule);
                    $vRule = $params[0];
                    unset($params[0]);

                    $vParams = [];

                    foreach ($params as $param) {
                        if (strpos($rule, ":") === false) {
                            $vParams[] = trim($param);
                        } else {
                            $p = explode(":", $param);
                            $vParams[trim($p[0])] = trim($p[1]);
                        }
                    }
                    $vData = new Data($field, array_dot($this->input, $field), $vParams, $this->input);
                }

                if (isset($availableValidators[$vRule])) {
                    $validation = $this->validate($vRule, $vData);

                    if ($validation['result'] === false) {
                        $this->passes = false;

                        $args = [
                            $validation['error_key'],
                            $this->getTranslator()->trans($field),
                        ];

                        if (isset($validation['error_params'])) {
                            foreach ($validation['error_params'] as $param) {
                                if (is_array($param)) {
                                    if ($param[1] === true) {
                                        array_push($args, $this->getTranslator()->trans($param[0]));
                                    } else {
                                        array_push($args, $param[0]);
                                    }
                                } else {
                                    array_push($args, $param);
                                }
                            }
                        }

                        $errorMessage = call_user_func_array([$this->getTranslator(), 'trans'], $args);
                        $this->getErrorBag()->addError($field, $errorMessage);

                        if ($this->breakChain) {
                            break;
                        }
                    }
                } else {
                    throw new \InvalidArgumentException('The validator "'.$vRule.'" has not been registered.');
                }
            }
        }
    }

    /**
     * Performs a validation
     * @param String $rule An alias of an existing validator
     * @param Object $data A Data-Object with validation data
     */
    protected function validate($rule, $data)
    {
        $validator = self::getValidators()[$rule];
        if (($validator instanceof \Closure)) {
            $result = $validator($data);
        } else {
            $result = (new $validator($data, self::$customFieldAliases))->run();
        }

        return $result;
    }

    /**
     * Generating all the error messages
     * @param   Object \Ilch\Translator $translator The translator instance
     * @return Array  An array with translated error messages
     */
    public function getErrors($translator = null)
    {
        return $this->getErrorBag()->getErrors();
    }

    /**
     * Creates a new validation instance
     * @param   Array   $input       An array with inputs (e.g. user inputs)
     * @param   Array   $rules       An array with validation rules
     * @param Boolean [$breakChain = true]    Whether the validation should stop on validation errors or not
     * @param Boolean [$autoRun    = true]    If false you have to manually run the validation
     * @returns Object  A new Validation Object
     */
    public static function create($input, $rules, $breakChain = true, $autoRun = true)
    {
        return new Validation($input, $rules, $breakChain, $autoRun);
    }

    /**
     * Returns the validation result
     * @return Boolean
     */
    public function isValid()
    {
        return $this->passes;
    }


    /**
     * Checks if the specified field has a validation error
     * @param   String  $field Field name
     * @return Boolean
     */
    public function hasError($field)
    {
        return in_array($field, $this->fields_with_error);
    }

    /**
     * Returns an array with all field names which have validation errors
     * @return Array
     */
    public function getFieldsWithError()
    {
        return $this->getErrorBag()->getErrorFields();
    }

    /**
     * Adds the specified validator
     * @param String        $alias     An alias for this validator
     * @param Object|String $validator This must be a string pointing to a valid class or a Closure
     */
    public static function addValidator($alias, $validator)
    {
        if (isset(self::$builtInValidators[$alias]) || isset(self::$validators[$alias])) {
            throw new \InvalidArgumentException('Validator alias "'.$alias.'" is already in use.');
        }

        if (!(is_object($validator) && ($validator instanceof \Closure))
            && (is_string($validator) && !class_exists($validator))) {
            throw new \InvalidArgumentException('Validator "'.$alias.'" is not a valid class or closure');
        }

        self::$validators[$alias] = $validator;
    }

    /**
     * Gets all validators (added and builtIn combined)
     * @return Array All Validators known at this time during runtime
     */
    public static function getValidators()
    {
        return self::$validators + self::$builtInValidators;
    }

    public static function setCustomFieldAliases($aliases)
    {
        self::$customFieldAliases = $aliases;
    }

    public function getErrorBag()
    {
        return $this->errorBag;
    }

    public function getTranslator()
    {
        return $this->translator;
    }
}
