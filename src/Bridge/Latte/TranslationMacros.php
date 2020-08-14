<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Latte;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use function strpos;
use function trim;
use function var_export;

final class TranslationMacros extends MacroSet
{

	public static function install(Compiler $compiler): void
	{
		$macros = new static($compiler);
		$macros->addMacro('_', [$macros, 'macroTranslateBegin'], [$macros, 'macroTranslateEnd']);
	}

	public function macroTranslateBegin(MacroNode $node, PhpWriter $writer): string
	{
		if ($node->args !== '') {
			$node->empty = true;
			if ($this->haveNoParameters($node)) {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');
			}

			return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
		}

		return '';
	}

	public function macroTranslateEnd(MacroNode $node, PhpWriter $writer): string
	{
		if (strpos($node->content, '<?php') === false) {
			$value = var_export($node->content, true);
			$node->content = '';
		} else {
			$node->openingCode = '<?php ob_start(function () {}) ?>' . $node->openingCode;
			$value = 'ob_get_clean()';
		}

		return $writer->write('$_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $_fi, %raw))', $node->context[0], $value);
	}

	private function haveNoParameters(MacroNode $node): bool
	{
		$result = trim($node->tokenizer->joinUntil(',')) === trim($node->args);
		$node->tokenizer->reset();

		return $result;
	}

}
