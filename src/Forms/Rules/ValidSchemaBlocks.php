<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Forms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;

/**
 * Laravel validation rule that rejects a structured-data repeater whose blocks
 * build malformed JSON-LD, deferring entirely to the core SchemaValidator via
 * {@see SEOSchemaFields::validateBlocks()}.
 *
 * A dedicated rule object (rather than an inline closure in ->rules()) is used
 * because Filament evaluates bare closures in a rules array as rule *factories*;
 * a ValidationRule instance is passed through to the validator untouched.
 */
class ValidSchemaBlocks implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (SEOSchemaFields::validateBlocks(is_array($value) ? $value : []) as $message) {
            $fail($message);
        }
    }
}
