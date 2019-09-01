<?php

use Luxio\Exceptions\ValidationException;
use Luxio\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function testValidationClassCreation()
    {
        $validator = new Validator();

        $this->assertInstanceOf(Validator::class, $validator);
    }

    public function testRequiredFailed()
    {
        go(function () {
            try {
                $validator = new Validator();
                $data = [];

                $validator->validate($data, [
                    'name' => 'required',
                    'age' => 'required'
                ]);
            } catch (\Throwable $th) {
                $message = json_decode($th->getMessage(), 1);
                $this->assertInstanceOf(ValidationException::class, $th);
                $this->assertIsArray($message);
                $this->assertArrayHasKey('name', $message[0]);
            }
        });
    }

    public function testRequiredPassed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'name' => 'John Doe'
            ];

            $data = $validator->validate($data, [
                'name' => 'required'
            ]);

            $this->assertArrayHasKey('name', $data);
        });
    }

    public function testNumericFailed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'age' => 'tHis Is a nUmBEr'
            ];

            try {
                $validator->validate($data, [
                    'age' => 'numeric'
                ]);
            } catch (\Throwable $th) {
                $message = json_decode($th->getMessage(), 1);
                $this->assertInstanceOf(ValidationException::class, $th);
                $this->assertIsArray($message);
                $this->assertArrayHasKey('age', $message[0]);
            }
        });
    }

    public function testNumericPassed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'age' => '21'
            ];

            $data = $validator->validate($data, [
                'age' => 'numeric'
            ]);

            $this->assertArrayHasKey('age', $data);
        });
    }

    public function testBoolFailed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'retard' => 'NO!'
            ];

            try {
                $validator->validate($data, [
                    'retard' => 'bool'
                ]);
            } catch (\Throwable $th) {
                $message = json_decode(
                    $th->getMessage(),
                    1
                );
                $this->assertInstanceOf(ValidationException::class, $th);
                $this->assertIsArray($message);
                $this->assertArrayHasKey('retard', $message[0]);
            }
        });
    }

    public function testBoolPassed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'retard' => false
            ];

            $data = $validator->validate($data, [
                'retard' => 'bool'
            ]);

            $this->assertArrayHasKey('retard', $data);
        });
    }

    public function testStringFailed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'dream' => 152025
            ];

            try {
                $validator->validate($data, [
                    'dream' => 'string'
                ]);
            } catch (\Throwable $th) {
                $message = json_decode(
                    $th->getMessage(),
                    1
                );
                $this->assertInstanceOf(ValidationException::class, $th);
                $this->assertIsArray($message);
                $this->assertArrayHasKey('dream', $message[0]);
            }
        });
    }

    public function testStringPassed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'dream' => 'Making my life worth living'
            ];

            $data = $validator->validate($data, [
                'dream' => 'string'
            ]);

            $this->assertArrayHasKey('dream', $data);
        });
    }

    public function testArrayFailed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'wishes' => 'You die now!'
            ];
            try {
                $validator->validate($data, [
                    'wishes' => 'array'
                ]);
            } catch (\Throwable $th) {
                $message = json_decode(
                    $th->getMessage(),
                    1
                );
                $this->assertInstanceOf(ValidationException::class, $th);
                $this->assertIsArray($message);
                $this->assertArrayHasKey('wishes', $message[0]);
            }
        });
    }

    public function testArrayPassed()
    {
        go(function () {
            $validator = new Validator();
            $data = [
                'wishes' => [
                    'Try Hard', 'Try Harder', 'Try Hardest'
                ]
            ];
            $data = $validator->validate($data, [
                'wishes' => 'array'
            ]);
            $this->assertArrayHasKey('wishes', $data);
        });
    }
}
