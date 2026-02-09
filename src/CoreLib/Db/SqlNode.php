<?php

namespace CoreLib\Db;

/**
 * SQL Value Object - Tagged Union for SQL Expressions
 * 
 * Represents SQL expressions (functions, casts, arrays, etc.) that serialize at SQL generation time.
 * Default behavior: serialize to literals (debug-friendly)
 * SqlNode::param() creates bind parameters explicitly
 * 
 * @example
 * 
 * // Function with literals
 * SqlNode::func('concat', ['prefix_', 'value'])
 * // → concat('prefix_', 'value')
 * 
 * // Function with bind parameter
 * SqlNode::func('concat', ['prefix_', SqlNode::param('id')])
 * // → concat('prefix_', :id)
 * 
 * // PostgreSQL array
 * SqlNode::pgArray(['php', 'pgsql'])
 * // → '{"php","pgsql"}'
 * 
 * // Cast (driver-specific)
 * SqlNode::cast(SqlNode::column('title'), 'tsvector')
 * // PostgreSQL: title::tsvector
 * // MySQL: CAST(title AS tsvector)
 */
class SqlNode
{
    protected const TYPE_COLUMN = 1;
    protected const TYPE_PARAM = 2;
    protected const TYPE_FUNC = 3;
    protected const TYPE_CAST = 4;
    protected const TYPE_PG_ARRAY = 5;
    protected const TYPE_IN_LIST = 6;
    protected const TYPE_RAW = 7;

    protected int $type;
    protected $value;
    protected array $args;
    protected ?string $cast;

    /**
     * @param int $type Node type: column, param, func, cast, pg_array, in_list, raw
     * @param mixed $value Primary value for the node
     * @param array $args Function arguments (for func type)
     * @param string|null $cast Cast type name (for cast type)
     */
    protected function __construct(
        int $type,
        mixed $value = null,
        array $args = [],
        ?string $cast = null
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->args = $args;
        $this->cast = $cast;
    }

    /**
     * Column reference (unquoted identifier)
     * @param string $name Column name
     * @return self
     */
    public static function column(string $name): self
    {
        return new self(self::TYPE_COLUMN, $name);
    }

    /**
     * Bind parameter reference
     * @param string $name Parameter name (without colons)
     * @return self
     */
    public static function param(string $name): self
    {
        return new self(self::TYPE_PARAM, $name);
    }

    /**
     * SQL function call
     * @param string $name Function name
     * @param array $args Function arguments (scalars or SqlNodes)
     * @return self
     */
    public static function func(string $name, array $args): self
    {
        return new self(self::TYPE_FUNC, $name, $args);
    }

    /**
     * Type cast (driver-specific syntax)
     * @param mixed $value Value to cast (scalar or SqlNode)
     * @param string $type Target type name
     * @return self
     */
    public static function cast(mixed $value, string $type): self
    {
        return new self(self::TYPE_CAST, $value, [], $type);
    }

    /**
     * PostgreSQL array literal
     * @param array $values Array elements (scalars or SqlNodes)
     * @return self
     */
    public static function pgArray(array $values): self
    {
        return new self(self::TYPE_PG_ARRAY, $values);
    }

    /**
     * Comma-separated list (for IN clauses)
     * @param array $values List elements (scalars or SqlNodes)
     * @return self
     */
    public static function inList(array $values): self
    {
        return new self(self::TYPE_IN_LIST, $values);
    }

    /**
     * Raw SQL (unescaped, passed through as-is)
     * @param string $sql Raw SQL string
     * @return self
     */
    public static function raw(string $sql): self
    {
        return new self(self::TYPE_RAW, $sql);
    }

    /**
     * Serialize node to SQL string
     * 
     * @param string $driver Database driver (mysql, pgsql, sqlite)
     * @param callable $serialize Callback for serializing scalar values
     *                            Signature: fn(mixed $value, bool $param = false): string
     * @return string SQL fragment
     */
    public function toSql(string $driver, callable $serialize): string
    {
        switch ($this->type) {
            // Column reference - pass through unquoted
            case self::TYPE_COLUMN:
                return $this->value;

            // Parameter reference - format as :name: for binding
            case self::TYPE_PARAM:
                return $serialize($this->value, true);

            // Raw SQL - pass through as-is
            case self::TYPE_RAW:
                return $this->value;

            // Function call - serialize arguments in literal mode by default
            case self::TYPE_FUNC:
                return $this->value . '(' .
                    implode(', ', array_map(
                        fn($a) => $a instanceof self
                        ? $a->toSql($driver, $serialize)
                        : $serialize($a),
                        $this->args
                    )) . ')';

            // Type cast - driver-specific syntax
            case self::TYPE_CAST:
                $expr = $this->value instanceof self
                    ? $this->value->toSql($driver, $serialize)
                    : $serialize($this->value);
                if ($driver === 'pgsql') {
                    return "$expr::{$this->cast}";
                }
                return "CAST($expr AS {$this->cast})";

            // PostgreSQL array literal
            case self::TYPE_PG_ARRAY:
                //return $this->serializePgArray($driver, $serialize);
                $escaped = array_map(
                    fn($v) => $v instanceof self
                    ? $v->toSql($driver, $serialize)
                    : $serialize($v),
                    $this->value
                );

                return "'{" . implode(',', $escaped) . "}'";

            // Comma-separated list (for IN clauses)
            case self::TYPE_IN_LIST:
                return implode(', ', array_map(
                    fn($v) => $v instanceof self
                    ? $v->toSql($driver, $serialize)
                    : $serialize($v),
                    $this->value
                ));

            default:
                throw new \LogicException("Unknown SqlNode type: {$this->type}");
        }
    }
}
