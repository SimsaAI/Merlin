<?php

namespace Merlin\Mvc;

/**
 * Class to map models
 */
class ModelMapping
{
	/**
	 * @var array
	 */
	protected array $mapping;


	/**
	 * Create ModelMapping from array config
	 * @param array $mapping
	 * @return static
	 */
	public static function fromArray(array $mapping): static
	{
		$instance = new self();
		foreach ($mapping ?? [] as $name => $config) {
			if (!\is_string($name)) {
				throw new \InvalidArgumentException('Model name must be a string');
			}
			if (\is_string($config)) {
				// "User" => "users"
				$config = [
					'source' => $config,
					'schema' => null,
				];
			} elseif (empty($config['source'])) {
				throw new \InvalidArgumentException("Model source cannot be empty for model '$name'");
			} elseif (!\is_string($config['source'])) {
				throw new \InvalidArgumentException("Model source must be a string for model '$name'");
			} elseif (!isset($config['schema'])) {
				$config['schema'] = null;
			} elseif (!\is_string($config['schema'])) {
				throw new \InvalidArgumentException("Model schema must be a string for model '$name'");
			}
			$instance->mapping[$name] = $config;
		}
		return $instance;
	}

	/**
	 * Add model mapping
	 * @param string $name
	 * @param string|null $source
	 * @param string|null $schema
	 * @return $this
	 */
	public function add(string $name, ?string $source = null, ?string $schema = null): static
	{
		if (empty($name)) {
			throw new \InvalidArgumentException('Model name cannot be empty');
		}
		if (empty($source)) {
			// AdminUserFlags -> admin_user_flags
			$source = static::toSnakeCase($name);
		}
		$this->mapping[$name] = [
			'source' => $source,
			'schema' => $schema,
		];
		return $this;
	}

	/**
	 * Get model mapping by name
	 * @param string $name
	 * @return array|null
	 */
	public function get(string $name): ?array
	{
		return $this->mapping[$name] ?? null;
	}


	/**
	 * Get all model mappings as an array
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->mapping;
	}

	/**
	 * Convert a string to snake_case.
	 * Handles various input formats, including camelCase, PascalCase, kebab-case, and space-separated words.
	 * Consecutive uppercase letters are treated as acronyms (e.g., XMLParser → xml_parser).
	 * Multiple separators are unified into a single underscore, and duplicate underscores are avoided.
	 *
	 * @param string $name The input string to convert.
	 * @return string The converted snake_case string.
	 */
	public static function toSnakeCase(string $name): string
	{
		// unify separators
		$name = str_replace(['-', '.', ' '], '_', $name);

		$result = '';
		$length = strlen($name);
		$isUnderscore = false; // ensure initialization

		for ($i = 0; $i < $length; $i++) {
			$char = $name[$i];
			$isUpper = ctype_upper($char);

			if ($isUpper) {
				// previous char exists and is:
				// - lowercase
				// - digit
				// - uppercase followed by lowercase (XMLParser → xml_parser)
				if (
					$i > 0 &&
					(
						ctype_lower($name[$i - 1]) ||
						ctype_digit($name[$i - 1]) ||
						($i + 1 < $length && ctype_lower($name[$i + 1]))
					)
				) {
					if (!$isUnderscore) {
						$result .= '_';
						$isUnderscore = true;
					}
				}

				$char = strtolower($char);

			} elseif ($char === '_') {
				if ($isUnderscore) {
					continue; // skip duplicate underscores
				}
				$isUnderscore = true;
				$result .= '_';
				continue;
			}

			$isUnderscore = false;
			$result .= $char;
		}

		return $result;
	}

}
