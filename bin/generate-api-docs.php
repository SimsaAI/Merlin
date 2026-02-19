#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param as ParamTag;
use phpDocumentor\Reflection\DocBlock\Tags\Return_ as ReturnTag;
use phpDocumentor\Reflection\DocBlock\Tags\Throws as ThrowsTag;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated as DeprecatedTag;

$srcDir = 'src';
$docsDir = 'docs/api';

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
                // Attempt autoloading; class_exists loads via Composer autoloader
                if (class_exists($fqcn)) {
                    $allClasses[$className] = $fqcn;
                    $namespacedClasses[substr($namespace, 0, -1)][] = $className;
                } else {
                    // optional fallback (commented out because side effects possible)
                    // require_once $file->getPathname();
                    // if (class_exists($fqcn)) $allClasses[$fqcn] = $fqcn;
                }
            }
        }
    }
}

echo "📝 Generating docs for " . count($allClasses) . " classes...\n";

// Generate index
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0755, true);
}
$indexContent = "# Merlin MVC API\n\n## Classes overview\n\n";
$sep = '';
foreach ($namespacedClasses as $namespace => $classes) {
    $indexContent .= $sep;
    $sep = "\n";
    $indexContent .= "### `{$namespace}`\n\n";
    $lastNamespace = $namespace;
    foreach ($classes as $class) {
        $fqcn = $allClasses[$class];
        $indexContent .= "- [{$class}]({$class}.md) `{$fqcn}`\n";
    }
}
file_put_contents("$docsDir/index.md", $indexContent . "\n");

// Individual class docs
foreach ($allClasses as $class) {
    $reflector = new ReflectionClass($class);
    $md = generateClassDoc($reflector, $docFactory);
    $filename = basename(str_replace('\\', '/', $class)) . '.md';
    file_put_contents("$docsDir/$filename", $md);
    echo "  ✓ {$filename}\n";
}

echo "✅ API docs ready: $docsDir/\n";

/* ---------------- Functions ---------------- */

function generateClassDoc(ReflectionClass $reflector, $docFactory)
{
    $md = "# 🧩 {$reflector->getName()}\n\n";

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
            // If DocBlock cannot be parsed, fallback to raw text
            $md .= trim(cleanDocBlock($doc)) . "\n\n";
        }
    }

    // Constants
    $constants = $reflector->getConstants();
    if (!empty($constants)) {
        $md .= "## 📌 Constants\n\n";
        foreach ($constants as $name => $value) {
            $md .= "- **{$name}** = `" . var_export($value, true) . "`\n";
        }
        $md .= "\n";
    }

    // Properties
    $props = $reflector->getProperties();
    if (!empty($props)) {
        $md .= "## 🔐 Properties\n\n";
        foreach ($props as $prop) {
            $vis = getVisibility($prop);
            $static = $prop->isStatic() ? ' static' : '';
            $type = formatReflectionType($prop->getType());
            $type = decorateType($type);
            $md .= "- `{$vis}{$static} {$type} \${$prop->getName()}`\n";
        }
        $md .= "\n";
    }

    // Public methods
    $md .= "## 🚀 Public methods\n\n";
    foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->class !== $reflector->name)
            continue;
        $md .= generateMethodDoc($method, $docFactory);
    }

    return $md;
}

function generateMethodDoc(ReflectionMethod $method, $docFactory)
{
    $md = "### `{$method->getName()}()`\n\n";

    // Signature
    $vis = getVisibility($method);
    $static = $method->isStatic() ? ' static' : '';
    $params = [];
    foreach ($method->getParameters() as $p) {
        $params[] = formatParameterSignature($p);
    }
    $returnType = formatReflectionType($method->getReturnType());
    $signature = "`{$vis}{$static} function {$method->getName()}(" . implode(', ', $params) . ") : {$returnType}`";
    $md .= $signature . "\n\n";

    // DocBlock via phpDocumentor
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
            // fallback: raw cleaned docblock
            $raw = cleanDocBlock($doc);
            if ($raw)
                $md .= $raw . "\n\n";
        }
    }

    // Parameters table with multiline descriptions
    if ($method->getNumberOfParameters() > 0) {
        $md .= "**🧭 Parameters**\n\n";
        $md .= "| Name | Type | Default | Description |\n";
        $md .= "|---|---|---|---|\n";
        $paramTags = $block ? $block->getTagsByName('param') : [];
        $paramTagMap = mapParamTags($paramTags);
        foreach ($method->getParameters() as $p) {
            $name = '$' . $p->getName();
            $type = formatReflectionType($p->getType());
            $type = decorateType($type);
            $type = str_replace('|', '\\|', $type); // escape pipe for markdown
            $default = $p->isDefaultValueAvailable() ? formatDefaultValue($p->getDefaultValue()) : '';
            $desc = $paramTagMap[$p->getName()] ?? $paramTagMap[$p->getPosition()] ?? '';
            $desc = $desc ? str_replace("\n", "<br>", trim($desc)) : '';
            $md .= "| `{$name}` | `{$type}` | `{$default}` | {$desc} |\n";
        }
        $md .= "\n";
    }

    // Return
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
    $md .= "- Type: `{$returnType}`\n";
    if ($returnDesc) {
        $md .= "- Description: " . str_replace("\n", "<br>", $returnDesc) . "\n";
    }
    $md .= "\n";

    // Throws
    if ($block && $block->hasTag('throws')) {
        $md .= "**⚠️ Throws**\n\n";
        foreach ($block->getTagsByName('throws') as $t) {
            if ($t instanceof ThrowsTag) {
                $md .= "- " . trim((string) $t->getType()) . " " . trim((string) $t->getDescription()) . "\n";
            } else {
                $md .= "- " . safeTagToString($t) . "\n";
            }
        }
        $md .= "\n";
    }

    return $md;
}

/* ---------------- Helper ---------------- */

function mapParamTags(array $paramTags): array
{
    $map = [];
    foreach ($paramTags as $tag) {
        if ($tag instanceof ParamTag) {
            $varName = $tag->getVariableName(); // e.g. '$id' or 'id'
            $desc = (string) $tag->getDescription();
            $varName = $varName ? ltrim($varName, '$') : null;
            if ($varName) {
                $map[$varName] = $desc;
            } else {
                $map[] = $desc; // positional fallback
            }
        } else {
            // Fallback: tag not parsed as Param (InvalidTag etc.)
            $text = trim(safeTagToString($tag));
            if ($text === '') {
                continue;
            }
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
    // Remove tags
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

function formatParameterSignature(ReflectionParameter $p): string
{
    $parts = [];
    $type = $p->getType();
    if ($type)
        $parts[] = formatReflectionType($type);
    $byRef = $p->isPassedByReference() ? '&' : '';
    $variadic = $p->isVariadic() ? '...' : '';
    $name = '$' . $p->getName();
    $default = '';
    if ($p->isDefaultValueAvailable() && !$p->isVariadic()) {
        $default = ' = ' . formatDefaultValue($p->getDefaultValue());
    }
    return implode(' ', array_filter([$parts ? implode(' ', $parts) : null, $byRef . $variadic . $name])) . $default;
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
        'string' => "🔤 $type",
        'int' => "🔢 $type",
        'float' => "🌡️ $type",
        'bool' => "⚙️ $type",
        'array' => "📦 $type",
        'object' => "🧱 $type",
        'mixed' => "🎲 $type",
        'null' => "∅ $type",
        default => $type,
    };
}
