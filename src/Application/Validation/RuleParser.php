<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation;

use InvalidArgumentException;
use SimpleRouter\Domain\Contracts\ValidationRule;
use SimpleRouter\Application\Validation\Rules\{
    RequiredRule,
    EmailRule,
    UuidRule,
    MinLengthRule,
    MaxLengthRule,
    MaxValueRule,
    MinValueRule,
    NumericRule,
    IntegerRule,
    AlphaRule,
    AlphaNumericRule,
    UrlRule,
    InRule,
    RegexRule,
    DateRule,
    BooleanRule
};

/**
 * Parses validation rule strings into ValidationRule objects
 */
final class RuleParser
{
    private const RULE_MAP = [
        'required' => RequiredRule::class,
        'email' => EmailRule::class,
        'uuid' => UuidRule::class,
        'uuidv4' => UuidRule::class,
        'uuidv1' => UuidRule::class,
        'numeric' => NumericRule::class,
        'integer' => IntegerRule::class,
        'int' => IntegerRule::class,
        'alpha' => AlphaRule::class,
        'alphanumeric' => AlphaNumericRule::class,
        'url' => UrlRule::class,
        'boolean' => BooleanRule::class,
        'bool' => BooleanRule::class,
    ];

    /**
     * Parse a rule string like "required|email|min:3|onError('Custom message')"
     * 
     * @return ValidationRule[]
     */
    public function parse(string $ruleString): array
    {
        $rules = [];
        $customMessage = $this->extractCustomMessage($ruleString);
        
        // Remove the onError part for processing
        $ruleString = preg_replace('/\|?onError\([^)]+\)/', '', $ruleString);
        
        $parts = explode('|', $ruleString);
        
        // Check if this is a numeric field (has numeric or integer rule)
        $isNumericField = false;
        foreach ($parts as $part) {
            $ruleName = explode(':', trim($part))[0];
            if (in_array($ruleName, ['numeric', 'integer', 'int'], true)) {
                $isNumericField = true;
                break;
            }
        }
        
        foreach ($parts as $part) {
            if (empty(trim($part))) {
                continue;
            }

            $rule = $this->parseRule(trim($part), $isNumericField);
            $rules[] = $rule;
        }

        // If we have a custom message and rules, apply it to the last one
        if ($customMessage && !empty($rules)) {
            $lastRule = $rules[count($rules) - 1];
            
            // Only apply message if it's an AbstractRule instance
            if (method_exists($lastRule, 'withMessage')) {
                $lastRule->withMessage($customMessage);
            }
        }

        return $rules;
    }

    private function parseRule(string $rulePart, bool $isNumericField = false): ValidationRule
    {
        // Check for parameterized rules like min:5, max:10, in:a,b,c
        if (str_contains($rulePart, ':')) {
            [$ruleName, $params] = explode(':', $rulePart, 2);
            return $this->createParameterizedRule($ruleName, $params, $isNumericField);
        }

        // Simple rules without parameters
        return $this->createSimpleRule($rulePart);
    }

    private function createSimpleRule(string $ruleName): ValidationRule
    {
        $ruleClass = self::RULE_MAP[$ruleName] ?? null;

        if (!$ruleClass) {
            throw new InvalidArgumentException("Unknown validation rule: {$ruleName}");
        }

        // Handle UUID variants
        if ($ruleName === 'uuidv4') {
            return new UuidRule('v4');
        }
        if ($ruleName === 'uuidv1') {
            return new UuidRule('v1');
        }

        return new $ruleClass();
    }

    private function createParameterizedRule(string $ruleName, string $params, bool $isNumericField = false): ValidationRule
    {
        return match ($ruleName) {
            'min' => $isNumericField 
                ? new MinValueRule((float) $params) 
                : new MinLengthRule((int) $params),
            'max' => $isNumericField 
                ? new MaxValueRule((float) $params) 
                : new MaxLengthRule((int) $params),
            'in' => new InRule(explode(',', $params)),
            'regex' => new RegexRule($params),
            'date' => new DateRule($params),
            'uuid' => new UuidRule($params),
            default => throw new InvalidArgumentException("Unknown parameterized rule: {$ruleName}")
        };
    }

    private function extractCustomMessage(string $ruleString): ?string
    {
        if (preg_match('/onError\([\'"]([^\'"]+)[\'"]\)/', $ruleString, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
