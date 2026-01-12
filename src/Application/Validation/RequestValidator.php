<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation;

use SimpleRouter\Domain\Contracts\Validator;

/**
 * Main validator implementation
 */
final class RequestValidator implements Validator
{
    public function __construct(
        private readonly RuleParser $parser = new RuleParser()
    ) {}

    public function validate(array $data, array $rules): ValidationResult
    {
        $result = new ValidationResult();

        foreach ($rules as $field => $ruleString) {
            $this->validateField($field, $data[$field] ?? null, $ruleString, $result);
        }

        return $result;
    }

    private function validateField(
        string $field,
        mixed $value,
        string $ruleString,
        ValidationResult $result
    ): void {
        $rules = $this->parser->parse($ruleString);

        foreach ($rules as $rule) {
            if (!$rule->validate($value)) {
                $result->addError($field, $rule->message());
                // Stop on first error for this field
                break;
            }
        }
    }
}
