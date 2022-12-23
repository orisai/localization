<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Latte;

use Closure;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\FilterNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Tag;
use Latte\Engine;
use Latte\Essential\Nodes\PrintNode;
use Latte\Essential\Nodes\TranslateNode;
use Latte\Essential\TranslatorExtension as OriginalExtension;
use Latte\Extension;
use Orisai\Localization\Translator;
use function array_unshift;
use function is_array;

final class TranslationExtension extends Extension
{

	/** @var Closure(literal-string, array<mixed>, string|null): string */
	private Closure $translator;

	private ?string $key;

	public function __construct(Translator $translator, ?string $key = null)
	{
		$this->translator = static fn (string $message,
			array $parameters = [],
			?string $languageTag = null
		): string => $translator->translate($message, $parameters, $languageTag);
		$this->key = $key;
	}

	public function getTags(): array
	{
		return [
			'_' => [$this, 'parseTranslate'],
			'translate' => fn (Tag $tag) =>

yield from TranslateNode::create(
	$tag,
	$this->key !== null ? $this->translator : null,
),
		];
	}

	public function getFilters(): array
	{
		return [
			'translate' => $this->translator,
		];
	}

	public function getCacheKey(Engine $engine): mixed
	{
		return $this->key;
	}

	public function parseTranslate(Tag $tag): Node
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();

		$node = new PrintNode();
		$node->expression = $tag->parser->parseUnquotedStringOrExpression();

		$args = new ArrayNode();
		if ($tag->parser->stream->tryConsume(',') !== null) {
			$args = $tag->parser->parseArguments();
		}

		$node->modifier = $tag->parser->parseModifier();
		$node->modifier->escape = true;

		if ($this->key !== null
			&& ($expr = OriginalExtension::toValue($node->expression)) !== null
			&& is_array($values = OriginalExtension::toValue($args))
		) {
			$node->expression = new StringNode(($this->translator)($expr, ...$values));

			return $node;
		}

		array_unshift($node->modifier->filters, new FilterNode(new IdentifierNode('translate'), $args->toArguments()));

		return $node;
	}

}
