#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use phpDocumentor\Reflection\DocBlock\Tags\Deprecated as DeprecatedTag;
use phpDocumentor\Reflection\DocBlock\Tags\Param as ParamTag;
use phpDocumentor\Reflection\DocBlock\Tags\Return_ as ReturnTag;
use phpDocumentor\Reflection\DocBlock\Tags\Throws as ThrowsTag;
use phpDocumentor\Reflection\DocBlockFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

$srcDir = 'src';
$docsDir = 'docs/api';
$projectRoot = dirname(__DIR__);

echo "🔍 Scanning $srcDir...\n";

$docFactory = DocBlockFactory::createInstance();

// Find all classes
$allClasses = [];
$namespacedClasses = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
            $namespace = '';
            if (preg_match('/namespace\s+([^\s;{]+)/', $content, $ns)) {
                $namespace = trim($ns[1]) . '\\';
            }
            foreach ($matches[1] as $className) {
                $fqcn = $namespace . $className;
                if (class_exists($fqcn)) {
                    $ns = substr($namespace, 0, -1);
                    $allClasses[$fqcn] = [
                        'short' => $className,
                        'namespace' => $ns,
                    ];
                    $namespacedClasses[$ns][] = $fqcn;
                }
            }
        }
    }
}

echo "📝 Generating docs for " . count($allClasses) . " classes...\n";

// Build class registry: maps FQCN and short name -> metadata for link generation.
// All src links are relative from docs/api/ (two levels up to project root).
$classRegistry = [];
$shortNameCounts = [];
foreach ($allClasses as $classMeta) {
    $shortName = $classMeta['short'];
    $shortNameCounts[$shortName] = ($shortNameCounts[$shortName] ?? 0) + 1;
}

foreach ($allClasses as $fqcn => $classMeta) {
    $shortName = $classMeta['short'];
    $ref = new ReflectionClass($fqcn);
    $absFile = $ref->getFileName();
    $relFromRoot = str_replace('\\', '/', substr($absFile, strlen($projectRoot) + 1));
    $srcLink = '../../' . $relFromRoot;
    $docFile = makeDocFileName($fqcn);
    $classRegistry[$fqcn] = [
        'short' => $shortName,
        'srcLink' => $srcLink . '#L' . $ref->getStartLine(),
        'srcFile' => $srcLink,
        'docLink' => $docFile,
    ];
    // Also index by short name only if unique
    if (($shortNameCounts[$shortName] ?? 0) === 1) {
        $classRegistry[$shortName] = &$classRegistry[$fqcn];
    }
}

// Generate index
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0755, true);
}
foreach (glob($docsDir . '/*.md') as $oldDocFile) {
    @unlink($oldDocFile);
}
$indexContent = "# Merlin MVC API\n\n## Classes overview\n\n";
$sep = '';
foreach ($namespacedClasses as $namespace => $classes) {
    $indexContent .= $sep;
    $sep = "\n";
    $indexContent .= "### `{$namespace}`\n\n";
    foreach ($classes as $fqcn) {
        $class = $allClasses[$fqcn]['short'];
        $indexContent .= "- [{$class}](" . makeDocFileName($fqcn) . ") `{$fqcn}`\n";
    }
}
file_put_contents("$docsDir/index.md", $indexContent . "\n");

// Individual class docs
foreach ($allClasses as $class => $classMeta) {
    $reflector = new ReflectionClass($class);
    $md = generateClassDoc($reflector, $docFactory, $classRegistry);
    $filename = makeDocFileName($class);
    file_put_contents("$docsDir/$filename", $md);
    echo "  ✓ {$filename}\n";
}

echo "✅ API docs ready: $docsDir/\n";

/* ---------------- Functions ---------------- */

function generateClassDoc(ReflectionClass $reflector, $docFactory, array $classRegistry): string
{
    $shortName = $reflector->getShortName();
    $fqcn = $reflector->getName();

    $srcInfo = $classRegistry[$fqcn] ?? null;
    $classLink = $srcInfo ? "[{$fqcn}]({$srcInfo['srcFile']})" : "`{$fqcn}`";
    $md = "# 🧩 {$shortName}\n\n";
    $md .= "**Full name:** {$classLink}\n\n";

    // Class DocComment
    if ($doc = $reflector->getDocComment()) {
        try {
            $block = $docFactory->create($doc);
            $summary = trim((string) $block->getSummary());
            $desc = trim((string) $block->getDescription());
            if ($summary)
                $md .= $summary . "\n\n";
            if ($desc)
                $md .= $desc . "\n\n";
            if ($block->hasTag('deprecated')) {
                $tag = current($block->getTagsByName('deprecated'));
                $md .= "**🛑 Deprecated**: " . safeTagToString($tag) . "\n\n";
            }
        } catch (Throwable $e) {
            $md .= trim(cleanDocBlock($doc)) . "\n\n";
        }
    }

    // Constants (hide protected)
    $constants = array_filter(
        $reflector->getReflectionConstants(),
        fn(ReflectionClassConstant $constant) => $constant->isPublic()
    );
    if (!empty($constants)) {
        $md .= "## 📌 Constants\n\n";
        foreach ($constants as $constant) {
            $name = $constant->getName();
            $value = $constant->getValue();
            $md .= "- **{$name}** = `" . var_export($value, true) . "`\n";
        }
        $md .= "\n";
    }

    // Properties (hide protected)
    $props = array_filter(
        $reflector->getProperties(),
        fn(ReflectionProperty $prop) => $prop->isPublic()
    );
    if (!empty($props)) {
        $md .= "## 🔐 Properties\n\n";
        foreach ($props as $prop) {
            $vis = getVisibility($prop);
            $static = $prop->isStatic() ? ' static' : '';
            $typeStr = formatReflectionType($prop->getType());
            $linkedType = linkType($typeStr, $classRegistry, 'doc');
            $propSrcLink = ($srcInfo && method_exists($prop, 'getStartLine'))
                ? ($srcInfo['srcFile'] . '#L' . $prop->getStartLine())
                : ($srcInfo ? $srcInfo['srcFile'] : null);
            $srcRef = $propSrcLink ? " · [source]($propSrcLink)" : '';
            $md .= "- `{$vis}{$static}` {$linkedType} `\${$prop->getName()}`{$srcRef}\n";
        }
        $md .= "\n";
    }

    // Public methods
    $md .= "## 🚀 Public methods\n\n";
    foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->class !== $reflector->name)
            continue;
        $md .= generateMethodDoc($method, $docFactory, $classRegistry);
    }

    return $md;
}

function generateMethodDoc(ReflectionMethod $method, $docFactory, array $classRegistry): string
{
    $classSrcInfo = $classRegistry[$method->class] ?? null;
    $methodSrcLink = $classSrcInfo ? ($classSrcInfo['srcFile'] . '#L' . $method->getStartLine()) : null;
    $srcBadge = $methodSrcLink ? " · [source]({$methodSrcLink})" : '';
    $md = "### {$method->getName()}(){$srcBadge}\n\n";

    // Build linked signature
    $vis = getVisibility($method);
    $static = $method->isStatic() ? ' static' : '';
    $linkedParams = [];
    foreach ($method->getParameters() as $p) {
        $linkedParams[] = formatParameter($p);
    }
    $returnTypeStr = formatReflectionType($method->getReturnType());
    $linkedReturn = linkType($returnTypeStr, $classRegistry, 'doc', true);

    // Signature: wrap keywords/name in backticks; types rendered as inline links
    $md .= "`{$vis}{$static} function {$method->getName()}(";
    $md .= implode(', ', $linkedParams);
    $md .= "): $returnTypeStr`\n\n";

    // DocBlock
    $doc = $method->getDocComment() ?: '';
    $block = null;
    if ($doc) {
        try {
            $block = $docFactory->create($doc);
            $summary = trim((string) $block->getSummary());
            $desc = trim((string) $block->getDescription());
            if ($summary)
                $md .= $summary . "\n\n";
            if ($desc)
                $md .= $desc . "\n\n";
            if ($block->hasTag('deprecated')) {
                $tag = current($block->getTagsByName('deprecated'));
                $md .= "**🛑 Deprecated**: " . safeTagToString($tag) . "\n\n";
            }
        } catch (Throwable $e) {
            $raw = cleanDocBlock($doc);
            if ($raw)
                $md .= $raw . "\n\n";
        }
    }

    // Parameters table
    if ($method->getNumberOfParameters() > 0) {
        $md .= "**🧭 Parameters**\n\n";
        $md .= "| Name | Type | Default | Description |\n";
        $md .= "|---|---|---|---|\n";
        $paramTags = $block ? $block->getTagsByName('param') : [];
        $paramTagMap = mapParamTags($paramTags);
        foreach ($method->getParameters() as $p) {
            $name = '$' . $p->getName();
            $typeStr = formatReflectionType($p->getType());
            // Escape union | separators for table cells, without touching link syntax
            $linkedTypeForTable = escapeTablePipes(linkType($typeStr, $classRegistry, 'doc'));
            $default = $p->isDefaultValueAvailable() ? formatDefaultValue($p->getDefaultValue()) : null;
            $desc = $paramTagMap[$p->getName()] ?? $paramTagMap[$p->getPosition()] ?? '';
            $desc = $desc ? str_replace("\n", "<br>", trim($desc)) : '';
            if (isset($default)) {
                $default = "`{$default}`";
            } else {
                $default = '-';
            }
            $md .= "| `{$name}` | {$linkedTypeForTable} | {$default} | {$desc} |\n";
        }
        $md .= "\n";
    }

    // Return value
    $returnDesc = '';
    if ($block && $block->hasTag('return')) {
        $ret = current($block->getTagsByName('return'));
        if ($ret instanceof ReturnTag) {
            $returnDesc = (string) $ret->getDescription();
        } else {
            $returnDesc = safeTagToString($ret);
        }
    }
    $md .= "**➡️ Return value**\n\n";
    $md .= "- Type: " . $linkedReturn . "\n";
    if ($returnDesc) {
        $md .= "- Description: " . str_replace("\n", "<br>", $returnDesc) . "\n";
    }
    $md .= "\n";

    // Throws
    if ($block && $block->hasTag('throws')) {
        $md .= "**⚠️ Throws**\n\n";
        foreach ($block->getTagsByName('throws') as $t) {
            if ($t instanceof ThrowsTag) {
                $exTypeStr = ltrim(trim((string) $t->getType()), '\\');
                $exDesc = trim((string) $t->getDescription());
                $linkedExType = linkType($exTypeStr, $classRegistry, 'doc');
                $md .= "- " . $linkedExType . ($exDesc ? "  " . $exDesc : "") . "\n";
            } else {
                $md .= "- " . safeTagToString($t) . "\n";
            }
        }
        $md .= "\n";
    }

    return $md;
}

/* ---------------- Helpers ---------------- */

function makeDocFileName(string $fqcn): string
{
    $parts = explode('\\', ltrim($fqcn, '\\'));
    if (count($parts) > 1 && $parts[0] === 'Merlin') {
        array_shift($parts);
    }
    $parts = array_map(
        fn(string $part): string => preg_replace('/[^A-Za-z0-9_]/', '_', $part),
        $parts
    );
    return implode('_', $parts) . '.md';
}

/**
 * Convert a type string (may contain | or & separators) into markdown with
 * inline links for known Merlin classes. Unrecognised types pass through
 * decorateType() which adds emojis for primitives and backtick-wraps the rest.
 */
/**
 * @param string $mode 'doc' = link to API .md page, 'src' = link to source file
 * @param bool $decorate Whether to decorate unrecognized types with emojis and backticks
 */
function linkType(string $typeStr, array $classRegistry, string $mode = 'doc', bool $decorate = false): string
{
    if ($typeStr === '')
        return '';
    $decorate = false;

    // Split on | and & while keeping the delimiters
    $parts = preg_split('/([|&])/', $typeStr, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = '';
    foreach ($parts as $part) {
        if ($part === '|' || $part === '&') {
            $result .= $part;
            continue;
        }
        $part = trim($part);
        $lookup = ltrim($part, '\\');    // strip leading \ from docblock FQCNs

        if (isset($classRegistry[$lookup])) {
            $info = $classRegistry[$lookup];
            $target = $mode === 'src' ? $info['srcLink'] : $info['docLink'];
            $name = $info['short'];
            if ($decorate) {
                $name = "🧩`{$name}`";
            }
            $result .= "[{$name}]({$target})";
        } else {
            $result .= $decorate ? decorateType($part) : $part;
        }
    }
    return $result;
}

/**
 * Escape only the union/intersection | separators for use inside a markdown
 * table cell, without touching | characters inside link URL parentheses.
 */
function escapeTablePipes(string $linkedType): string
{
    $result = '';
    $depth = 0;
    for ($i = 0, $len = strlen($linkedType); $i < $len; $i++) {
        $ch = $linkedType[$i];
        if ($ch === '(') {
            $depth++;
            $result .= $ch;
        } elseif ($ch === ')') {
            $depth--;
            $result .= $ch;
        } elseif ($ch === '|' && $depth === 0) {
            $result .= '\|';
        } else {
            $result .= $ch;
        }
    }
    return $result;
}

/**
 * Format a single method parameter as a linked-type + `$name` fragment
 * suitable for embedding inline in the signature line.
 */
function formatLinkedParameter(ReflectionParameter $p, array $classRegistry): string
{
    $typeStr = formatReflectionType($p->getType());
    $linkedType = $typeStr ? linkType($typeStr, $classRegistry, 'doc', false) : '';
    $byRef = $p->isPassedByReference() ? '&' : '';
    $variadic = $p->isVariadic() ? '...' : '';
    $namePart = '`' . $byRef . $variadic . '$' . $p->getName();
    if ($p->isDefaultValueAvailable() && !$p->isVariadic()) {
        $namePart .= ' = ' . formatDefaultValue($p->getDefaultValue());
    }
    $namePart .= '`';
    return $linkedType ? ($linkedType . ' ' . $namePart) : $namePart;
}

function formatParameter(ReflectionParameter $p): string
{
    $typeStr = formatReflectionType($p->getType());
    $byRef = $p->isPassedByReference() ? '&' : '';
    $variadic = $p->isVariadic() ? '...' : '';
    $namePart = $byRef . $variadic . '$' . $p->getName();
    if ($p->isDefaultValueAvailable() && !$p->isVariadic()) {
        $namePart .= ' = ' . formatDefaultValue($p->getDefaultValue());
    }
    return $typeStr ? ($typeStr . ' ' . $namePart) : $namePart;
}

function mapParamTags(array $paramTags): array
{
    $map = [];
    foreach ($paramTags as $tag) {
        if ($tag instanceof ParamTag) {
            $varName = $tag->getVariableName();
            $desc = (string) $tag->getDescription();
            $varName = $varName ? ltrim($varName, '$') : null;
            if ($varName) {
                $map[$varName] = $desc;
            } else {
                $map[] = $desc;
            }
        } else {
            $text = trim(safeTagToString($tag));
            if ($text === '')
                continue;
            if (preg_match('/^\$?([a-zA-Z0-9_]+)\b(.*)$/s', $text, $m)) {
                $name = $m[1];
                $desc = trim($m[2]);
                if ($name) {
                    $map[$name] = $desc;
                    continue;
                }
            }
            $map[] = $text;
        }
    }
    return $map;
}

function safeTagToString($tag): string
{
    try {
        return (string) $tag;
    } catch (Throwable $e) {
        return '';
    }
}

function cleanDocBlock(string $doc): string
{
    $clean = preg_replace('/^\s*\/\*\*|\*\/\s*$/', '', $doc);
    $clean = preg_replace('/^\s*\*\s?/m', '', trim($clean));
    $clean = preg_replace('/@[\w]+\s+.*$/ms', '', $clean);
    return trim($clean);
}

function formatReflectionType(?ReflectionType $type): string
{
    if ($type === null)
        return 'mixed';

    // Intersection types (PHP 8.1+)
    if (class_exists('ReflectionIntersectionType') && $type instanceof ReflectionIntersectionType) {
        $names = [];
        foreach ($type->getTypes() as $t) {
            $names[] = $t instanceof ReflectionNamedType ? $t->getName() : 'mixed';
        }
        return implode('&', $names);
    }

    // Union types
    if ($type instanceof ReflectionUnionType) {
        $names = [];
        foreach ($type->getTypes() as $t) {
            $names[] = $t instanceof ReflectionNamedType ? $t->getName() : 'mixed';
        }
        return implode('|', $names);
    }

    // Named type
    if ($type instanceof ReflectionNamedType) {
        $name = $type->getName();
        if ($type->allowsNull() && $name !== 'mixed') {
            return $name . '|null';
        }
        return $name;
    }

    return 'mixed';
}

function formatDefaultValue($value): string
{
    if (is_null($value))
        return 'null';
    if (is_bool($value))
        return $value ? 'true' : 'false';
    if (is_string($value))
        return "'" . str_replace("'", "\\'", $value) . "'";
    if (is_array($value))
        return '[]';
    if (is_object($value))
        return get_class($value);
    return (string) $value;
}

function getVisibility(ReflectionMethod|ReflectionProperty $r): string
{
    if ($r->isPrivate())
        return 'private';
    if ($r->isProtected())
        return 'protected';
    return 'public';
}

function decorateType(string $type): string
{
    return match ($type) {
        'string' => "🔤 `string`",
        'int' => "🔢 `int`",
        'float' => "🌡️ `float`",
        'bool' => "⚙️ `bool`",
        'array' => "📦 `array`",
        'object' => "🧱 `object`",
        'mixed' => "🎲 `mixed`",
        'null' => "`null`",
        'void' => "`void`",
        'never' => "`never`",
        'self' => "🧩 `self`",
        'static' => "🧩 `static`",
        default => "`{$type}`",
    };
}
