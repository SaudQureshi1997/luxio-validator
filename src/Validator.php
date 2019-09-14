<?php

namespace Elphis\Utils;

use BadMethodCallException;
use Elphis\Exceptions\ValidationException;
use Closure;
use Swoole\Coroutine\Channel;

class Validator
{
    /**
     * available validations
     *
     * @var array
     */
    protected $validations;

    /**
     * Error Channel for parallel validation
     *
     * @var Channel
     */
    protected $channel;

    /**
     * error messages
     *
     * @var array
     */
    protected $messages;

    /**
     * input data to be validated
     *
     * @var array
     */
    protected $inputs = [];

    public function __construct()
    {
        $this->channel = new Channel(1);
        $this->registerMessages();
        $this->registerValidations();
    }

    /**
     * registers the messages
     *
     * @return void
     */
    public function registerMessages()
    {
        $this->messages = [
            'array' => ':attribute must be an array',
            'string' => ':attribute must be a string',
            'bool' => ':attribute must be a boolean',
            'numeric' => ':attribute must be a number',
            'required' => ':attribute is required',
            'max' => ':attribute must not be more than :value',
            'min' => ':attribute must not be less than :value',
            'digits' => ':attribute must contain :value digits',
            'date' => ':attribute must be a valid date',
            'email' => ':attribute must be a valid email',
            'present' => ':attribute must be present'
        ];
    }

    /**
     * registers the validations
     *
     * @return void
     */
    public function registerValidations()
    {
        $this->validations = [
            'required' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('required', $key)
                        ]
                    );
                }
            },

            'numeric' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (!is_numeric($this->inputs[$key])) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('numeric', $key)
                        ]
                    );
                }
            },

            'bool' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (filter_var($this->inputs[$key], FILTER_VALIDATE_BOOLEAN) === false) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('bool', $key)
                        ]
                    );
                }
            },

            'array' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (!\is_array($this->inputs[$key])) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('array', $key)
                        ]
                    );
                }
            },

            'string' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (!is_string($this->inputs[$key])) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('string', $key)
                        ]
                    );
                }
            },

            'digits' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (strlen($this->inputs[$key]) !== (int) $params[0]) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('digits', $key, $params[0])
                        ]
                    );
                }
            },

            'date' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                $d = DateTime::createFromFormat($params[0] ?? 'Y-m-d', $this->inputs[$key]);
                return $d && $d->format($params[0] ?? 'Y-m-d') === $this->inputs[$key];
            },

            'email' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (filter_var($this->inputs[$key], FILTER_VALIDATE_EMAIL) === false) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('email', $key)
                        ]
                    );
                }
            },

            'nullable' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }
            },

            'present' => function (string $key, $params = null) {
                if (!array_key_exists($key, $this->inputs)) {
                    $this->channel->push(
                        [
                            $key => $this->getMessage('present', $key)
                        ]
                    );
                }
            },

            'min' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (is_string($this->inputs[$key])) {
                    if (strlen($this->inputs[$key]) < $params[0]) {
                        return $this->channel->push(
                            [
                                $key => $this->getMessage('min', $params[0])
                            ]
                        );
                    }
                }

                if (is_array($this->inputs[$key])) {
                    if (count($this->inputs[$key]) < $params[0]) {
                        return $this->channel->push(
                            [
                                $key => $this->getMessage('min', $params[0])
                            ]
                        );
                    }
                }
            },

            'max' => function (string $key, $params = null) {
                if (empty($this->inputs[$key])) {
                    return;
                }

                if (is_string($this->inputs[$key])) {
                    if (strlen($this->inputs[$key]) > $params[0]) {
                        return $this->channel->push(
                            [
                                $key => $this->getMessage('max', $params[0])
                            ]
                        );
                    }
                }

                if (is_array($this->inputs[$key])) {
                    if (count($this->inputs[$key]) > $params[0]) {
                        return $this->channel->push(
                            [
                                $key => $this->getMessage('max', $params[0])
                            ]
                        );
                    }
                }
            }
        ];
    }

    /**
     * extend the core validator
     *
     * @param string $name
     * @param Closure $callable
     * @param string $message
     * @return void
     */
    public function extends($name, Closure $callable, $message)
    {
        $this->messages[$name] = $message;

        $this->validations[$name] = function ($key, $params = null) use ($callable, $name) {

            if (empty($this->inputs[$key])) {
                return;
            }

            $result = $callable($this->inputs[$key], $params);

            if ($result == false) {
                $value = '';
                if (is_array($params) and isset($params[0])) {
                    $value = $params[0];
                }
                $this->channel->push([
                    $key => $this->getMessage($name, $key, $value)
                ]);
            }
        };
    }

    /**
     * performs validation on the given input with the given rules
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @throws ValidationException
     * 
     * @return array
     */
    public function validate(array $inputs, array $rules): array
    {
        $this->inputs = $inputs;

        foreach ($rules as $key => $validations) {

            $validations = explode('|', $validations);

            foreach ($validations as $validation) {
                $params = null;
                $method = $validation;

                if (strpos($validation, ':') > -1) {
                    [$method, $params] = explode(':', $validation);
                }

                if (strpos($method, '_') > -1) {
                    $method = str_replace('_', '', $method);
                }

                if (strpos($params, ',') > -1) {
                    $params = explode(',', $params);
                }

                if (!is_array($params)) {
                    $params = [$params];
                }

                $method = strtolower($method);

                if (!array_key_exists($method, $this->validations)) {
                    throw new BadMethodCallException("$method does not exist on " . self::class);
                }

                go($this->validations[$method], $key, $params);
            }
        }

        $errors = [];

        while (!$this->channel->isEmpty()) {
            $result = $this->channel->pop();
            if ($result) {
                array_push($errors, $result);
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $inputs;
    }

    /**
     * get error message for an attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return string|null
     */
    protected function getMessage(string $rule, string $attribute, $value = null)
    {
        if (!empty($this->messages[$rule])) {
            $message = str_replace(':attribute', $attribute, $this->messages[$rule]);
            $message = str_replace(':value', $value, $message);

            return $message;
        }

        return null;
    }
}
