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
use Rankbeam\Seo\Filament\Support\ResolvesSeoTarget;
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
    use ResolvesSeoTarget;

    /**
     * Virtual form state path (NOT a column). The section reads/writes
     * `seo_meta.schema_jsonld` directly in its hydrate/save hooks, so the Group
     * is marked `dehydrated(false)` (its state is kept out of the model save)
     * and this path only namespaces the editor's working state.
     */
    public const STATE_PATH = 'seo_schema';

    /** schema.org availability tokens offered for a Product offer. */
    public const AVAILABILITY = ['InStock', 'OutOfStock', 'PreOrder', 'BackOrder', 'Discontinued'];

    /**
     * @param  \Closure|null  $target  Closure(?Model $formRecord): ?Model — write the
     *                                 schema onto a RELATED model instead of the form's
     *                                 own record. Null binds the form's record (default).
     *                                 Must resolve the SAME target as {@see SEOFields}.
     */
    public static function make(?\Closure $target = null): Section
    {
        return Section::make('Structured data')
            ->icon('heroicon-o-code-bracket-square')
            ->description('schema.org JSON-LD for rich results. Built from the fields below and rendered into the page — no code required.')
            ->schema([
                Group::make(self::fields())
                    ->statePath(self::STATE_PATH)
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (Group $component, ?Model $record) use ($target): void {
                        $target = self::resolveSeoTarget($target, $record, $component);

                        $stored = $target instanceof Model
                            ? self::currentMeta($target, app()->getLocale())?->schema_jsonld
                            : null;

                        $component->getChildSchema()->fill(self::decompose($target, $stored) + [
                            // Snapshot of what was in the column when the form
                            // opened, for the optimistic-concurrency check on save.
                            'original' => self::normalizeStored($stored),
                        ]);
                    })
                    ->saveRelationshipsUsing(function (Group $component, ?Model $record) use ($target): void {
                        $target = self::resolveSeoTarget($target, $record, $component);

                        // Null target (create form / not-yet-existing relation)
                        // or a model without the HasSEO contract: write nothing,
                        // and never auto-create a placeholder related model.
                        if (! $target instanceof Model || ! method_exists($target, 'seoMeta')) {
                            return;
                        }

                        $state = $component->getChildSchema()->getState();
                        $value = self::compose($target, $state);

                        $locale = app()->getLocale();
                        $existing = self::currentMeta($target, $locale);

                        // Optimistic concurrency: if the column changed since the
                        // form hydrated (a concurrent external write — e.g. the Pro
                        // dashboard's AI schema action), reconcile so newer
                        // documents this form never saw are not clobbered.
                        $value = self::reconcile(
                            $value,
                            $existing?->schema_jsonld,
                            is_array($state['original'] ?? null) ? $state['original'] : [],
                        );

                        if ($existing) {
                            $existing->update(['schema_jsonld' => $value]);
                        } elseif ($value !== null) {
                            $target->seoMeta()->create([
                                'schema_jsonld' => $value,
                                'locale' => $locale,
                            ]);
                        }

                        $target->unsetRelation('seoMeta');
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

            // Optimistic-concurrency baseline: the set of documents present in
            // schema_jsonld at hydration, captured as a flat list. On save it
            // lets the editor tell apart "documents I started from" from
            // "documents a concurrent process (e.g. the Pro dashboard's AI
            // action) added while this form was open" so the latter are not
            // clobbered. Never shown to the editor.
            Hidden::make('original')
                ->default([]),
        ];
    }

    protected static function currentMeta(Model $target, string $locale): ?Model
    {
        return method_exists($target, 'seoMetaForLocale')
            ? $target->seoMetaForLocale($locale)->first()
            : $target->seoMeta()->where('locale', $locale)->first();
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
     * Optimistic-concurrency reconciliation.
     *
     * The editor preserves unrepresentable schema only as a *hydration snapshot*
     * (the `custom` field). If another process writes `schema_jsonld` while the
     * form is open — for example the Pro dashboard's AI schema action — a naive
     * save would replace the column from that stale snapshot and silently delete
     * the newer document. This compares the column as it stands *now* against the
     * baseline captured at hydration: any document that appeared since (present
     * in `$current` but not in `$original`) is treated as a concurrent external
     * write and appended to the value this form intends to save, so newer data is
     * never lost. Documents the editor itself produced are not duplicated.
     *
     * @param  array<string, mixed>|array<int, array<string, mixed>>|null  $composed  what this form wants to save
     * @param  mixed  $current  the column value as it stands in the DB right now
     * @param  array<int, mixed>  $original  the documents present when the form hydrated
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    public static function reconcile(?array $composed, mixed $current, array $original): ?array
    {
        $currentDocs = self::normalizeStored($current);

        // Fast path: the column is untouched since hydration, nothing to merge.
        if (self::sameDocumentSet($currentDocs, $original)) {
            return $composed;
        }

        // Find documents that appeared concurrently (in the DB now, but not in
        // the baseline) and that this form is not already about to write.
        $result = self::normalizeStored($composed);

        foreach ($currentDocs as $doc) {
            if (! is_array($doc) || $doc === []) {
                continue;
            }

            if (self::containsDocument($original, $doc) || self::containsDocument($result, $doc)) {
                continue;
            }

            $result[] = $doc;
        }

        return match (count($result)) {
            0 => null,
            1 => $result[0],
            default => array_values($result),
        };
    }

    /**
     * Whether two document lists hold the same set (order-independent).
     *
     * @param  array<int, mixed>  $a
     * @param  array<int, mixed>  $b
     */
    protected static function sameDocumentSet(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $doc) {
            if (! self::containsDocument($b, $doc)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether $haystack already contains a document equal to $needle.
     *
     * @param  array<int, mixed>  $haystack
     */
    protected static function containsDocument(array $haystack, mixed $needle): bool
    {
        foreach ($haystack as $doc) {
            if ($doc == $needle) {
                return true;
            }
        }

        return false;
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
                // Provenance by structural skeleton, not by mere capability.
                //
                // The editor's ONLY way to author a top-level BreadcrumbList is
                // the auto toggle, and when that toggle is on compose() always
                // writes exactly BreadcrumbSchema::fromModelAncestors($record).
                // So a stored breadcrumb belongs to the toggle iff it has the
                // same *skeleton* as the one the record currently generates from
                // its live ancestor chain — the same ordered item URLs (the Home
                // item included) and the same depth.
                //
                // An ancestor rename only changes an item's `name` label, never
                // the skeleton, so a stale auto-breadcrumb is still recognized
                // and refreshed by compose() on save — no freeze, no duplicate.
                // A hand-authored or externally generated (e.g. a Pro AI action)
                // breadcrumb has a different skeleton — different URLs, depth, or
                // Home item — so it is NOT claimed and is preserved verbatim as
                // custom schema, never clobbered by an unrelated save.
                //
                // (An earlier fix claimed any breadcrumb whenever the record had
                // ancestors at all; that misclassified foreign breadcrumbs on a
                // record that happened to have ancestors — adversarial F5.)
                if (self::isOwnAutoBreadcrumb($record, $doc)) {
                    $autoBreadcrumb = true;
                } else {
                    // Either no live ancestor chain to regenerate from (e.g. the
                    // record was detached) or a foreign breadcrumb the editor did
                    // not author: preserve it verbatim so a save can't drop it.
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
     * Whether a stored top-level BreadcrumbList is one the editor's auto toggle
     * produced (and may therefore refresh) rather than a foreign document.
     *
     * Ownership is decided by comparing the stored breadcrumb's skeleton — its
     * ordered item URLs (Home included) — against the skeleton of the breadcrumb
     * the record currently generates from its live ancestor chain. Item `name`
     * labels are ignored, so an ancestor rename (which changes only labels) is
     * still recognized as the editor's own; a breadcrumb with different URLs,
     * a different depth, or a different Home item is foreign and preserved.
     *
     * @param  array<string, mixed>  $stored
     */
    protected static function isOwnAutoBreadcrumb(?Model $record, array $stored): bool
    {
        if (! $record instanceof Model) {
            return false;
        }

        $generated = BreadcrumbSchema::fromModelAncestors($record)?->toArray();

        if ($generated === null) {
            return false;
        }

        return self::breadcrumbSkeleton($stored) === self::breadcrumbSkeleton($generated);
    }

    /**
     * The structural skeleton of a BreadcrumbList: the ordered list of item URLs
     * (paired with their positions), with `name` labels deliberately dropped so
     * that an ancestor rename does not change the skeleton. Returns null when the
     * document is not a recognizable BreadcrumbList shape.
     *
     * @param  array<string, mixed>  $doc
     * @return array<int, array{position: mixed, item: mixed}>|null
     */
    protected static function breadcrumbSkeleton(array $doc): ?array
    {
        if (($doc['@type'] ?? null) !== 'BreadcrumbList') {
            return null;
        }

        $elements = $doc['itemListElement'] ?? null;

        if (! is_array($elements) || ! array_is_list($elements)) {
            return null;
        }

        $skeleton = [];

        foreach ($elements as $element) {
            if (! is_array($element)) {
                return null;
            }

            $skeleton[] = [
                'position' => $element['position'] ?? null,
                'item' => $element['item'] ?? null,
            ];
        }

        return $skeleton;
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
