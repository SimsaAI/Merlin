<?php

namespace Merlin\Validation;

/**
 * Fluent validator for a single input field.
 *
 * Chain rules to describe what the field must look like.
 * The validator is executed by {@see Validator} (or the nested model/list machinery)
 * via the internal {@see validate()} method.
 *
 * Example:
 *   $v->field('email')->required()->email()->max(255);
 *   $v->field('age')->optional()->int()->min(18)->max(120);
 *   $v->field('tags')->optional()->list(fn($f) => $f->string()->max(50));
 */
class FieldValidator
{
    private bool $requiredFlag = true;
    private bool $hasDefault = false;
    private mixed $defaultValue = null;

    // Type coercion rule: 'int' | 'float' | 'bool' | 'string' | null (pass-through)
    private ?string $typeRule = null;

    private ?float $min = null;
    private ?float $max = null;

    /** @var array<int, array{0: string, 1?: mixed}> */
    private array $formatRules = [];

    private ?FieldValidator $listSubValidator = null;

    /** @var array<string, callable>|null */
    private ?array $modelFields = null;

    // ---- Presence -----------------------------------------------------------

    public function required(): static
    {
        $this->requiredFlag = true;
        return $this;
    }

    public function optional(): static
    {
        $this->requiredFlag = false;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->requiredFlag;
    }

    /**
     * Supply a default value used when the field is absent.
     * Calling default() implicitly makes the field optional.
     * The default is included in validated() as-is (no rules are applied to it).
     */
    public function default(mixed $value): static
    {
        $this->hasDefault = true;
        $this->defaultValue = $value;
        $this->requiredFlag = false; // a default makes required() meaningless
        return $this;
    }

    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }

    public function getDefault(): mixed
    {
        return $this->defaultValue;
    }

    // ---- Type coercion ------------------------------------------------------

    /**
     * Coerce to integer. Accepts int values and numeric strings (including negatives).
     */
    public function int(): static
    {
        $this->typeRule = 'int';
        return $this;
    }

    /**
     * Coerce to float. Accepts any numeric value.
     */
    public function float(): static
    {
        $this->typeRule = 'float';
        return $this;
    }

    /**
     * Coerce to bool. Accepts true/false, 1/0, "true"/"false", "yes"/"no", "on"/"off".
     */
    public function bool(): static
    {
        $this->typeRule = 'bool';
        return $this;
    }

    /**
     * Explicitly cast to string. Useful for ensuring min/max applies to character length.
     */
    public function string(): static
    {
        $this->typeRule = 'string';
        return $this;
    }

    // ---- Constraints --------------------------------------------------------

    /**
     * Minimum value / length / count depending on type:
     *   - string: minimum character length (mb_strlen)
     *   - int/float: minimum numeric value
     *   - array: minimum number of items
     */
    public function min(int|float $n): static
    {
        $this->min = (float) $n;
        return $this;
    }

    /**
     * Maximum value / length / count (same semantics as min).
     */
    public function max(int|float $n): static
    {
        $this->max = (float) $n;
        return $this;
    }

    // ---- Format rules -------------------------------------------------------

    /** Value must be a valid e-mail address (RFC 5321). */
    public function email(): static
    {
        $this->formatRules[] = ['email'];
        return $this;
    }

    /** Value must be a valid URL (FILTER_VALIDATE_URL). */
    public function url(): static
    {
        $this->formatRules[] = ['url'];
        return $this;
    }

    /** Value must be a valid IPv4 or IPv6 address. */
    public function ip(): static
    {
        $this->formatRules[] = ['ip'];
        return $this;
    }

    /** Value must match the given regular expression. */
    public function pattern(string $regex): static
    {
        $this->formatRules[] = ['pattern', $regex];
        return $this;
    }

    /**
     * Value must be strictly equal (===) to one of the allowed values.
     *
     * @param array<mixed> $allowed
     */
    public function in(array $allowed): static
    {
        $this->formatRules[] = ['in', $allowed];
        return $this;
    }

    // ---- Structure rules ----------------------------------------------------

    /**
     * Value must be an array; each element is validated by the configured sub-validator.
     *
     * @param callable(FieldValidator): void $configure
     */
    public function list(callable $configure): static
    {
        $sub = new static();
        $configure($sub);
        $this->listSubValidator = $sub;
        return $this;
    }

    /**
     * Value must be an associative array matching the given field definitions.
     * Each entry maps a key name to a callable that configures a FieldValidator.
     *
     * @param array<string, callable(FieldValidator): void> $fields
     */
    public function model(array $fields): static
    {
        $this->modelFields = $fields;
        return $this;
    }

    // ---- Internal execution -------------------------------------------------

    /**
     * Apply all configured rules to $value, appending any errors to $errors.
     *
     * @param mixed  $value  The raw input value.
     * @param string $path   Dot-path used as the error key.
     * @param array<string, string> $errors  Accumulated errors (mutated in place).
     * @return mixed The coerced / validated value.
     */
    public function validate(mixed $value, string $path, array &$errors): mixed
    {
        // 1. Type coercion
        if ($this->typeRule !== null) {
            [$value, $ok] = $this->applyType($value, $path, $errors);
            if (!$ok) {
                return $value;
            }
        }

        // 2. Min / max
        if ($this->min !== null || $this->max !== null) {
            if (!$this->applyMinMax($value, $path, $errors)) {
                return $value;
            }
        }

        // 3. Format rules
        foreach ($this->formatRules as $rule) {
            if (!$this->applyFormat($value, $rule, $path, $errors)) {
                return $value;
            }
        }

        // 4. List / model structure
        if ($this->listSubValidator !== null) {
            $value = $this->applyList($value, $path, $errors);
        } elseif ($this->modelFields !== null) {
            $value = $this->applyModel($value, $path, $errors);
        }

        return $value;
    }

    // ---- Private helpers ----------------------------------------------------

    /**
     * @return array{0: mixed, 1: bool}  [coercedValue, success]
     */
    private function applyType(mixed $value, string $path, array &$errors): array
    {
        switch ($this->typeRule) {
            case 'int':
                if (is_int($value)) {
                    return [$value, true];
                }
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$path] = 'must be an integer';
                    return [$value, false];
                }
                return [(int) $value, true];

            case 'float':
                if (is_float($value) || is_int($value)) {
                    return [(float) $value, true];
                }
                if (!is_numeric($value)) {
                    $errors[$path] = 'must be a number';
                    return [$value, false];
                }
                return [(float) $value, true];

            case 'bool':
                if (is_bool($value)) {
                    return [$value, true];
                }
                $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                if ($result === null) {
                    $errors[$path] = 'must be a boolean (true/false, yes/no, on/off, 1/0)';
                    return [$value, false];
                }
                return [$result, true];

            case 'string':
                return [(string) $value, true];

            default:
                return [$value, true];
        }
    }

    private function applyMinMax(mixed $value, string $path, array &$errors): bool
    {
        if (is_string($value)) {
            $n = mb_strlen($value);
            $noun = 'characters';
        } elseif (is_int($value) || is_float($value)) {
            $n = $value;
            $noun = null;
        } elseif (is_array($value)) {
            $n = count($value);
            $noun = 'items';
        } else {
            return true;
        }

        if ($this->min !== null && $n < $this->min) {
            $min = (int) $this->min === $this->min ? (int) $this->min : $this->min;
            $errors[$path] = $noun
                ? "must have at least {$min} {$noun}"
                : "must be at least {$min}";
            return false;
        }

        if ($this->max !== null && $n > $this->max) {
            $max = (int) $this->max === $this->max ? (int) $this->max : $this->max;
            $errors[$path] = $noun
                ? "must have at most {$max} {$noun}"
                : "must be at most {$max}";
            return false;
        }

        return true;
    }

    /**
     * @param array{0: string, 1?: mixed} $rule
     */
    private function applyFormat(mixed $value, array $rule, string $path, array &$errors): bool
    {
        switch ($rule[0]) {
            case 'email':
                if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$path] = 'must be a valid email address';
                    return false;
                }
                break;

            case 'url':
                if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                    $errors[$path] = 'must be a valid URL';
                    return false;
                }
                break;

            case 'ip':
                if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                    $errors[$path] = 'must be a valid IP address';
                    return false;
                }
                break;

            case 'pattern':
                if (!preg_match($rule[1], (string) $value)) {
                    $errors[$path] = 'has an invalid format';
                    return false;
                }
                break;

            case 'in':
                if (!in_array($value, $rule[1], true)) {
                    $errors[$path] = 'must be one of: ' . implode(', ', array_map('strval', $rule[1]));
                    return false;
                }
                break;
        }

        return true;
    }

    private function applyList(mixed $value, string $path, array &$errors): mixed
    {
        if (!is_array($value)) {
            $errors[$path] = 'must be an array';
            return $value;
        }

        $result = [];
        foreach ($value as $i => $item) {
            $subPath = $path . '[' . $i . ']';
            $errorsBefore = count($errors);
            $sub = $this->listSubValidator->validate($item, $subPath, $errors);
            if (count($errors) === $errorsBefore) {
                $result[$i] = $sub;
            }
        }

        return $result;
    }

    private function applyModel(mixed $value, string $path, array &$errors): mixed
    {
        if (!is_array($value)) {
            $errors[$path] = 'must be an object';
            return $value;
        }

        $result = [];
        foreach ($this->modelFields as $key => $configure) {
            $fv = new static();
            $configure($fv);
            $subPath = $path === '' ? $key : $path . '.' . $key;

            if (!array_key_exists($key, $value)) {
                if ($fv->isRequired()) {
                    $errors[$subPath] = 'required';
                } elseif ($fv->hasDefault()) {
                    $result[$key] = $fv->getDefault();
                }
                continue;
            }

            $errorsBefore = count($errors);
            $sub = $fv->validate($value[$key], $subPath, $errors);
            if (count($errors) === $errorsBefore) {
                $result[$key] = $sub;
            }
        }

        return $result;
    }
}
