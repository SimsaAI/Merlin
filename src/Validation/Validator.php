<?php

namespace Merlin\Validation;

/**
 * Validates and coerces an associative input array against a set of field rules.
 *
 * Usage:
 *
 *   $v = new Validator($request->post());
 *
 *   $v->field('email')->required()->email()->max(255);
 *   $v->field('age')->required()->int()->min(18)->max(120);
 *   $v->field('name')->optional()->string()->min(2)->max(100);
 *   $v->field('tags')->optional()->list(fn($f) => $f->string()->max(50));
 *   $v->field('address')->optional()->model([
 *       'street' => fn($f) => $f->required()->string(),
 *       'zip'    => fn($f) => $f->required()->pattern('/^\d{5}$/'),
 *   ]);
 *
 *   if ($v->fails()) {
 *       return Response::json(['errors' => $v->errors()], 422);
 *   }
 *   $data = $v->validated();
 *
 * Or in a single call (throws ValidationException on failure):
 *
 *   $data = $v->validate();
 */
class Validator
{
    /** @var array<string, FieldValidator> */
    private array $fields = [];

    /** @var array<string, string> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $validatedData = [];

    private bool $ran = false;

    /**
     * @param array<string, mixed> $data Raw input array (e.g. from Request::post()).
     */
    public function __construct(private array $data)
    {
    }

    /**
     * Register rules for a field and return the fluent FieldValidator.
     *
     * Fields default to required. Call ->optional() on the returned validator
     * to make the field optional.
     */
    public function field(string $name): FieldValidator
    {
        $fv = new FieldValidator();
        $this->fields[$name] = $fv;
        $this->ran = false; // invalidate any previous run
        return $fv;
    }

    /**
     * Run all rules. Returns true when at least one rule failed.
     */
    public function fails(): bool
    {
        $this->run();
        return !empty($this->errors);
    }

    /**
     * Dot-path keyed error messages from the last run.
     * Empty when validation has not been run yet or all rules passed.
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        $this->run();
        return $this->errors;
    }

    /**
     * Returns only the fields that passed validation, with values coerced to
     * their declared types. Fields that failed are excluded.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $this->run();
        return $this->validatedData;
    }

    /**
     * Run validation and return the validated data, or throw on failure.
     *
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }
        return $this->validatedData;
    }

    // ---- Private ------------------------------------------------------------

    private function run(): void
    {
        if ($this->ran) {
            return;
        }

        $this->ran = true;
        $this->errors = [];
        $this->validatedData = [];

        foreach ($this->fields as $name => $fv) {
            $exists = \array_key_exists($name, $this->data);

            if (!$exists) {
                if ($fv->isRequired()) {
                    $this->errors[$name] = 'required';
                } elseif ($fv->hasDefault()) {
                    $this->validatedData[$name] = $fv->getDefault();
                }
                continue;
            }

            $errorsBefore = \count($this->errors);
            $coerced = $fv->validate($this->data[$name], $name, $this->errors);

            if (\count($this->errors) === $errorsBefore) {
                $this->validatedData[$name] = $coerced;
            }
        }
    }
}
