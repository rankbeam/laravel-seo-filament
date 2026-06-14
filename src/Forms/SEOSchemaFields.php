<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Forms;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Rankbeam\Seo\Filament\Forms\Rules\ValidSchemaBlocks;
use Rankbeam\Seo\Services\Schema\BreadcrumbSchema;
use Rankbeam\Seo\Services\Schema\FAQSchema;
use Rankbeam\Seo\Services\Schema\ProductSchema;
use Rankbeam\Seo\Services\Schema\SchemaValidator;

/**
 * An optional structured-data (schema.org JSON-LD) editor for any Filament
 * resource form. Lets a content editor attach rich-result schema without
 * touching code, writing the result into the core's `seo_meta.schema_jsonld`
 * column (rendered by the core's TagRenderer / `renderSchema()`).
 *
 * It is pure UI binding: it never invents schema. Every document it writes is
 * produced by a core builder — {@see BreadcrumbSchema}, {@see FAQSchema},
 * {@see ProductSchema} — and validated through the core {@see SchemaValidator}
 * before it can be saved. A malformed block (e.g. an FAQ entry with no answer,
 * or a Product with no offer) is rejected at form-validation time.
 *
 * The section leads with a zero-config win: a single toggle that derives a
 * BreadcrumbList from the record's ancestor chain ({@see
 * BreadcrumbSchema::fromModelAncestors()}) — no fields to fill in.
 *
 * Add it next to {@see SEOFields} (or anywhere in the form):
 *
 * ```php
 * use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;
 *
 * public static function form(Schema $schema): Schema
 * {
 *     return $schema->components([
 *         // ... your fields ...
 *         SEOFields::make(),
 *         SEOSchemaFields::make(),
 *     ]);
 * }
 * ```
 *
 * The model behind the form must use the core HasSEO trait. Schema this editor
 * cannot represent (a hand-authored `@graph`, an exotic `@type`, or a Product
 * carrying fields the form doesn't expose such as reviews/ratings) is preserved
 * verbatim and round-trips untouched — the editor never clobbers it.
 */
class SEOSchemaFields
{
    /**
     * Virtual form state path (NOT a column). The section reads/writes
     * `seo_meta.schema_jsonld` directly in its hydrate/save hooks, so this path
     * is dehydrated and only namespaces the editor's working state.
     */
    public const STATE_PATH = 'seo_schema';

    /** schema.org availability tokens offered for a Product offer. */
    public const AVAILABILITY = ['InStock', 'OutOfStock', 'PreOrder', 'BackOrder', 'Discontinued'];

    public static function make(): Section
    {
        return Section::make('Structured data')
            ->icon('heroicon-o-code-bracket-square')
            ->description('schema.org JSON-LD for rich results. Built from the fields below and rendered into the page — no code required.')
            ->schema([
                Group::make(self::fields())
                    ->statePath(self::STATE_PATH)
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (Group $component, ?Model $record): void {
                        $stored = $record && method_exists($record, 'seoMeta')
                            ? ($record->seoMeta?->schema_jsonld)
                            : null;

                        $component->getChildSchema()->fill(self::decompose($record, $stored));
                    })
                    ->saveRelationshipsUsing(function (Group $component, Model $record): void {
                        if (! method_exists($record, 'seoMeta')) {
                            return;
                        }

                        $state = $component->getChildSchema()->getState();
                        $value = self::compose($record, $state);

                        $existing = $record->seoMeta()->first();

                        if ($existing) {
                            $existing->update(['schema_jsonld' => $value]);
                        } elseif ($value !== null) {
                            $record->seoMeta()->create([
                                'schema_jsonld' => $value,
                                'locale' => app()->getLocale(),
                            ]);
                        }

                        $record->unsetRelation('seoMeta');
                    }),
            ])
            ->collapsible()
            ->collapsed()
            ->columnSpanFull();
    }

    /**
     * @return array<int, Component>
     */
    protected static function fields(): array
    {
        return [
            // The zero-config win, led with: one toggle, no fields to fill.
            Toggle::make('auto_breadcrumb')
                ->label('Automatic breadcrumb')
                ->helperText('Generate a BreadcrumbList from this page\'s parent chain. Zero configuration — it follows the model\'s ancestors.')
                ->default(false),

            Repeater::make('blocks')
                ->label('Schema blocks')
                ->addActionLabel('Add structured data')
                ->reorderable(false)
                ->default([])
                ->columnSpanFull()
                ->rules([new ValidSchemaBlocks])
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'faq' => 'FAQ (Q&A)',
                            'product' => 'Product',
                        ])
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    // FAQ
                    Repeater::make('questions')
                        ->label('Questions')
                        ->addActionLabel('Add question')
                        ->visible(fn (Get $get): bool => $get('type') === 'faq')
                        ->default([])
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('question')
                                ->label('Question')
                                ->columnSpanFull(),
                            Textarea::make('answer')
                                ->label('Answer')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    // Product
                    TextInput::make('name')
                        ->label('Product name')
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    TextInput::make('brand')
                        ->label('Brand')
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    TextInput::make('image')
                        ->label('Image URL')
                        ->url()
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    TextInput::make('price')
                        ->label('Price')
                        ->numeric()
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    TextInput::make('currency')
                        ->label('Currency')
                        ->default('USD')
                        ->maxLength(3)
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                    Select::make('availability')
                        ->label('Availability')
                        ->options(array_combine(self::AVAILABILITY, self::AVAILABILITY))
                        ->visible(fn (Get $get): bool => $get('type') === 'product'),
                ]),

            // Preserves any stored schema this editor cannot represent
            // (hand-authored @graph, exotic @type, richer Product fields) so a
            // save never destroys it. Never shown to the editor. Left dehydrated
            // (default) so getChildSchema()->getState() returns it on save; the
            // parent Group's dehydrated(false) keeps it out of the model save.
            Hidden::make('custom')
                ->default([]),
        ];
    }

    // -----------------------------------------------------------------
    // Compose: editor state -> the value stored in schema_jsonld
    // -----------------------------------------------------------------

    /**
     * Build the value to persist into `seo_meta.schema_jsonld` from the editor
     * state. Returns null (clear) when nothing is configured, a single document
     * when there is exactly one, or a list of documents when there are several.
     * Order: breadcrumb first, then editor blocks, then preserved custom docs.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    public static function compose(?Model $record, array $state): ?array
    {
        $docs = [];

        if (($state['auto_breadcrumb'] ?? false) && $record instanceof Model) {
            $crumb = BreadcrumbSchema::fromModelAncestors($record)?->toArray();

            if ($crumb !== null) {
                $docs[] = $crumb;
            }
        }

        foreach (array_values($state['blocks'] ?? []) as $block) {
            $doc = is_array($block) ? self::blockToDocument($block) : null;

            if ($doc !== null) {
                $docs[] = $doc;
            }
        }

        foreach (array_values($state['custom'] ?? []) as $doc) {
            if (is_array($doc) && $doc !== []) {
                $docs[] = $doc;
            }
        }

        return match (count($docs)) {
            0 => null,
            1 => $docs[0],
            default => $docs,
        };
    }

    /**
     * Build one JSON-LD document from a single editor block via a core builder.
     * Returns null for an empty block (so an untouched added block is ignored).
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>|null
     */
    protected static function blockToDocument(array $block): ?array
    {
        return match ($block['type'] ?? null) {
            'faq' => self::faqDocument($block),
            'product' => self::productDocument($block),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>|null
     */
    protected static function faqDocument(array $block): ?array
    {
        $questions = self::cleanQuestions($block['questions'] ?? []);

        if ($questions === []) {
            return null;
        }

        return FAQSchema::fromArray($questions)->toArray();
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>|null
     */
    protected static function productDocument(array $block): ?array
    {
        $name = self::str($block['name'] ?? null);
        $description = self::str($block['description'] ?? null);
        $image = self::str($block['image'] ?? null);
        $brand = self::str($block['brand'] ?? null);
        $sku = self::str($block['sku'] ?? null);
        $price = $block['price'] ?? null;
        $availability = self::str($block['availability'] ?? null);

        // An entirely blank product block is treated as empty (ignored).
        if ($name === '' && $description === '' && $image === '' && $brand === ''
            && $sku === '' && ($price === null || $price === '') && $availability === '') {
            return null;
        }

        $product = new ProductSchema;

        if ($name !== '') {
            $product->setName($name);
        }
        if ($description !== '') {
            $product->setDescription($description);
        }
        if ($image !== '') {
            $product->setImage($image);
        }
        if ($brand !== '') {
            $product->setBrand($brand);
        }
        if ($sku !== '') {
            $product->setSku($sku);
        }
        if ($price !== null && $price !== '') {
            $currency = self::str($block['currency'] ?? null);
            $product->setPrice((float) $price, $currency !== '' ? $currency : 'USD');
        }
        if ($availability !== '') {
            $product->setAvailability($availability);
        }

        return $product->toArray();
    }

    // -----------------------------------------------------------------
    // Validation: reject malformed schema before it is saved
    // -----------------------------------------------------------------

    /**
     * Validate every non-empty editor block through the core SchemaValidator
     * and return human-readable rejection messages (empty = all valid).
     *
     * @param  array<int|string, mixed>  $blocks
     * @return array<int, string>
     */
    public static function validateBlocks(array $blocks): array
    {
        $validator = new SchemaValidator;
        $messages = [];

        foreach (array_values($blocks) as $block) {
            if (! is_array($block)) {
                continue;
            }

            $doc = self::blockToDocument($block);

            if ($doc === null) {
                continue;
            }

            $result = $validator->validate($doc);

            if ($result->isValid) {
                continue;
            }

            $label = match ($block['type'] ?? null) {
                'faq' => 'FAQ',
                'product' => 'Product',
                default => 'Schema',
            };

            foreach ($result->getErrorMessages() as $message) {
                $messages[] = $label.' schema: '.$message;
            }
        }

        return $messages;
    }

    // -----------------------------------------------------------------
    // Decompose: stored schema_jsonld -> editor state (round-trip)
    // -----------------------------------------------------------------

    /**
     * Turn the stored schema_jsonld back into editor state. Documents this
     * editor produced decompose into editable blocks (verified by rebuilding
     * them and comparing); everything else is preserved verbatim in `custom`.
     *
     * @return array{auto_breadcrumb: bool, blocks: array<int, array<string, mixed>>, custom: array<int, array<string, mixed>>}
     */
    public static function decompose(?Model $record, mixed $stored): array
    {
        $autoBreadcrumb = false;
        $blocks = [];
        $custom = [];

        foreach (self::normalizeStored($stored) as $doc) {
            if (! is_array($doc) || $doc === []) {
                continue;
            }

            $type = $doc['@type'] ?? null;

            if ($type === 'BreadcrumbList') {
                $auto = $record instanceof Model
                    ? BreadcrumbSchema::fromModelAncestors($record)?->toArray()
                    : null;

                if ($auto !== null && $auto == $doc) {
                    $autoBreadcrumb = true;
                } else {
                    $custom[] = $doc;
                }

                continue;
            }

            if ($type === 'FAQPage') {
                $questions = self::faqToQuestions($doc);

                if ($questions !== null && FAQSchema::fromArray($questions)->toArray() == $doc) {
                    $blocks[] = ['type' => 'faq', 'questions' => $questions];
                } else {
                    $custom[] = $doc;
                }

                continue;
            }

            if ($type === 'Product') {
                $fields = self::productToFields($doc);

                if ($fields !== null && self::productDocument($fields) == $doc) {
                    $blocks[] = ['type' => 'product'] + $fields;
                } else {
                    $custom[] = $doc;
                }

                continue;
            }

            $custom[] = $doc;
        }

        return [
            'auto_breadcrumb' => $autoBreadcrumb,
            'blocks' => $blocks,
            'custom' => $custom,
        ];
    }

    /**
     * Normalize the stored value to a flat list of candidate documents. A
     * single object becomes a one-element list; a `@graph` wrapper or any other
     * associative document is left whole (and will be preserved as custom).
     *
     * @return array<int, mixed>
     */
    protected static function normalizeStored(mixed $stored): array
    {
        if (! is_array($stored) || $stored === []) {
            return [];
        }

        return array_is_list($stored) ? $stored : [$stored];
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return array<int, array{question: string, answer: string}>|null
     */
    protected static function faqToQuestions(array $doc): ?array
    {
        $mainEntity = $doc['mainEntity'] ?? null;

        if (! is_array($mainEntity)) {
            return null;
        }

        $questions = [];

        foreach ($mainEntity as $entity) {
            if (! is_array($entity)) {
                return null;
            }

            $questions[] = [
                'question' => self::str($entity['name'] ?? null),
                'answer' => self::str($entity['acceptedAnswer']['text'] ?? null),
            ];
        }

        return $questions;
    }

    /**
     * Decompose a Product document into the editor's field set. Returns null
     * when the product carries a shape the form cannot represent (e.g. a
     * multi-image array or a plain-string brand) so it is preserved as custom.
     *
     * @param  array<string, mixed>  $doc
     * @return array<string, string>|null
     */
    protected static function productToFields(array $doc): ?array
    {
        $image = $doc['image'] ?? null;
        if ($image !== null && ! is_string($image)) {
            return null;
        }

        $brand = $doc['brand'] ?? null;
        if ($brand !== null && ! (is_array($brand) && ($brand['@type'] ?? null) === 'Brand')) {
            return null;
        }

        $offers = $doc['offers'] ?? [];
        if (! is_array($offers)) {
            return null;
        }

        return [
            'name' => self::str($doc['name'] ?? null),
            'description' => self::str($doc['description'] ?? null),
            'image' => self::str($image),
            'brand' => self::str(is_array($brand) ? ($brand['name'] ?? null) : null),
            'sku' => self::str($doc['sku'] ?? null),
            'price' => isset($offers['price']) ? self::str($offers['price']) : '',
            'currency' => self::str($offers['priceCurrency'] ?? null),
            'availability' => self::availabilityToken($offers['availability'] ?? null),
        ];
    }

    /**
     * Strip the schema.org URL prefix from an availability value, leaving the
     * bare token the Select offers (e.g. https://schema.org/InStock -> InStock).
     */
    protected static function availabilityToken(mixed $availability): string
    {
        $value = self::str($availability);

        if ($value === '') {
            return '';
        }

        $position = strrpos($value, '/');

        return $position === false ? $value : substr($value, $position + 1);
    }

    // -----------------------------------------------------------------

    /**
     * Trim a value of unknown type to a clean string ('' when absent).
     */
    protected static function str(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Normalize editor question rows: drop rows that are entirely blank, keep
     * partial ones (so the validator can reject a question with no answer).
     *
     * @return array<int, array{question: string, answer: string}>
     */
    protected static function cleanQuestions(mixed $questions): array
    {
        if (! is_array($questions)) {
            return [];
        }

        $clean = [];

        foreach (array_values($questions) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $question = self::str($row['question'] ?? null);
            $answer = self::str($row['answer'] ?? null);

            if ($question === '' && $answer === '') {
                continue;
            }

            $clean[] = ['question' => $question, 'answer' => $answer];
        }

        return $clean;
    }
}
