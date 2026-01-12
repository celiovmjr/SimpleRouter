<?php

declare(strict_types=1);

namespace SimpleRouter\Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Application\Validation\RequestValidator;
use SimpleRouter\Application\Validation\ValidationResult;

/**
 * Validation System Tests
 */
class ValidationTest extends TestCase
{
    private RequestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RequestValidator();
    }

    public function testRequiredRule(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testRequiredRuleFails(): void
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('name', $result->errors());
    }

    public function testEmailRule(): void
    {
        $data = ['email' => 'test@example.com'];
        $rules = ['email' => 'required|email'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testEmailRuleFails(): void
    {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'required|email'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('email', $result->errors());
    }

    public function testMinLengthRule(): void
    {
        $data = ['username' => 'john'];
        $rules = ['username' => 'required|min:3'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testMinLengthRuleFails(): void
    {
        $data = ['username' => 'jo'];
        $rules = ['username' => 'required|min:3'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testMaxLengthRule(): void
    {
        $data = ['username' => 'john'];
        $rules = ['username' => 'required|max:10'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testMaxLengthRuleFails(): void
    {
        $data = ['username' => 'verylongusername'];
        $rules = ['username' => 'required|max:10'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testIntegerRule(): void
    {
        $data = ['age' => '25'];
        $rules = ['age' => 'integer'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testIntegerRuleFails(): void
    {
        $data = ['age' => 'not-a-number'];
        $rules = ['age' => 'integer'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testNumericRule(): void
    {
        $data = ['price' => '19.99'];
        $rules = ['price' => 'numeric'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testAlphaRule(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'alpha'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testAlphaRuleFails(): void
    {
        $data = ['name' => 'John123'];
        $rules = ['name' => 'alpha'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testAlphaNumericRule(): void
    {
        $data = ['username' => 'john123'];
        $rules = ['username' => 'alphanumeric'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testUuidRule(): void
    {
        $data = ['id' => '550e8400-e29b-41d4-a716-446655440000'];
        $rules = ['id' => 'uuid'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testUuidRuleFails(): void
    {
        $data = ['id' => 'not-a-uuid'];
        $rules = ['id' => 'uuid'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testUuidV4Rule(): void
    {
        $data = ['id' => '550e8400-e29b-41d4-a716-446655440000'];
        $rules = ['id' => 'uuidv4'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testUrlRule(): void
    {
        $data = ['website' => 'https://example.com'];
        $rules = ['website' => 'url'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testUrlRuleFails(): void
    {
        $data = ['website' => 'not-a-url'];
        $rules = ['website' => 'url'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testBooleanRule(): void
    {
        $data = ['active' => true];
        $rules = ['active' => 'boolean'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testInRule(): void
    {
        $data = ['role' => 'admin'];
        $rules = ['role' => 'in:admin,user,guest'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testInRuleFails(): void
    {
        $data = ['role' => 'superadmin'];
        $rules = ['role' => 'in:admin,user,guest'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testCustomErrorMessage(): void
    {
        $data = ['email' => 'invalid'];
        $rules = ['email' => 'required|email|onError("Custom error message")'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $error = $result->firstError('email');
        $this->assertEquals('Custom error message', $error);
    }

    public function testMultipleRules(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => '25'
        ];

        $rules = [
            'name' => 'required|alpha|min:3|max:50',
            'email' => 'required|email',
            'age' => 'required|integer|min:18'
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testMultipleRulesWithErrors(): void
    {
        $data = [
            'name' => 'Jo',
            'email' => 'invalid-email',
            'age' => '15'
        ];

        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'age' => 'required|integer|min:18'
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $this->assertCount(3, $result->errors());
    }

    public function testDateRule(): void
    {
        $data = ['date' => '2024-01-05'];
        $rules = ['date' => 'date:Y-m-d'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testDateRuleFails(): void
    {
        $data = ['date' => 'invalid-date'];
        $rules = ['date' => 'date:Y-m-d'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }

    public function testRegexRule(): void
    {
        $data = ['code' => 'ABC123'];
        $rules = ['code' => 'regex:/^[A-Z]{3}[0-9]{3}$/'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testRegexRuleFails(): void
    {
        $data = ['code' => 'INVALID'];
        $rules = ['code' => 'regex:/^[A-Z]{3}[0-9]{3}$/'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result->isValid());
    }
}
