<?php

namespace Merlin\Cli;


/**
 * Base class for all CLI task classes.
 *
 * Extend this class to create a CLI task. Public methods ending in "Action"
 * are automatically discoverable by {@see Console}.
 */
abstract class Task
{
    /** @var Console The Console instance that is executing this task. */
    public Console $console;

    /** @var array<string, mixed> Parsed options from the command line. */
    public array $options = [];


    // -------------------------------------------------------------------------
    //  Output helpers – delegate to the Console for consistent color support
    // -------------------------------------------------------------------------

    protected function writeln(string $text = ''): void
    {
        $this->console->writeln($text);
    }

    protected function line(string $text): void
    {
        $this->console->line($text);
    }

    protected function info(string $text): void
    {
        $this->console->info($text);
    }

    protected function success(string $text): void
    {
        $this->console->success($text);
    }

    protected function warn(string $text): void
    {
        $this->console->warn($text);
    }

    protected function error(string $text): void
    {
        $this->console->error($text);
    }

    protected function muted(string $text): void
    {
        $this->console->muted($text);
    }

    /**
     * Apply one or more named ANSI styles to a string via the Console.
     * Style names: bold, dim, red, green, yellow, blue, magenta, cyan,
     *              white, gray, bred, bgreen, byellow, bcyan
     */
    protected function style(string $text, string ...$styles): string
    {
        return $this->console->style($text, ...$styles);
    }

    // -------------------------------------------------------------------------
    //  Option parsing helper
    // -------------------------------------------------------------------------

    /** 
     * Retrieve a parsed option value by key, with an optional default.
     * @param string $key The option name (without leading dashes).
     * @param mixed $default The default value to return if the option is not set.
     * @return mixed The option value or the default if not set.
     */
    public function opt(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

}
