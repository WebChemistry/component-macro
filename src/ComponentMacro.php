<?php

declare(strict_types=1);

namespace WebChemistry\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class ComponentMacro extends MacroSet {

	/** @var array */
	public $directories = [];

	/** @var array */
	protected $imports = [];

	/**
	 * @param Compiler $compiler
	 * @param string|array $directory
	 */
	public static function install(Compiler $compiler, $directory): void {
		$me = new self($compiler);
		$me->directories = (array) $directory;

		$me->addMacro('component', NULL, [$me, 'macroComponent']);
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string|null
	 */
	public function macroComponent(MacroNode $node, PhpWriter $writer): ?string {
		$isEmpty = !$node->content;
		$tagName = $node->htmlNode->name;
		$prepend = $isEmpty ? '' : '<?php ob_start(); ?> ';
		$isBlock = FALSE;

		// modifiers
		if (isset($node->htmlNode->attrs['modifiers'])) {
			$node->modifiers = $node->htmlNode->attrs['modifiers'];
		}
		if (isset($node->htmlNode->attrs['block'])) {
			$isBlock = TRUE;
		}

		list($file, $blockName) = $this->parsePath($node->htmlNode->name, $isBlock);

		$inside = $isEmpty ? 'NULL' : 'ob_get_clean()';
		$code = "// WebChemistry\\Macros\\ComponentMacro\n";
		$path = $this->getPath($file);
		if ($blockName) {
			if (!isset($this->imports[$file])) {
				$this->imports[$file] = TRUE;
				$code .= $writer->write(
						'$this->createTemplate(%var, $this->params, "import")->render();',
						$path
					) . "\n";
			}
			$code .= $writer->write(
				'$this->renderBlock(%word, ["_content" => ' . $inside . '] + %node.array? + $this->params %raw);',
				$blockName,
				$this->modify($node, $writer)
			);
		} else {
			$code .= $writer->write(
				'$this->createTemplate(%var, ["_content" => ' . $inside . '] + %node.array? + $this->params, "include")' .
				'->renderToContentType(%raw)',
				$path,
				$this->modify($node, $writer, '"html"', FALSE)
			);
		}

		$node->content = preg_replace("~<$tagName.*?>(.*)<\\/$tagName>~s", $prepend . $node->innerContent . '<?php ' . $code . ' ?>', $node->content);

		return NULL;
	}

	public function getPath(string $file): string {
		foreach ($this->directories as $directory) {
			if (file_exists($directory . $file)) {
				return $directory . $file;
			}
		}

		throw new \Exception("Template for file $file not found.");
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @param string $else
	 * @param bool $comma
	 * @return string
	 */
	private function modify(MacroNode $node, PhpWriter $writer, string $else = '', bool $comma = TRUE): string {
		if (!$node->modifiers) {
			return $else;
		}

		return $writer->write(
			($comma ? ', ' : '') . 'function ($s, $type) { $_fi = new LR\FilterInfo($type); return %modifyContent($s); }'
		);
	}

	/**
	 * @param string $name
	 * @param bool $isBlock
	 * @return array [directory, blockName]
	 */
	private function parsePath(string $name, bool $isBlock = FALSE) {
		preg_match_all('~[A-Z]?[^A-Z]+~', $name, $matches);
		$matches = array_map(function ($val) {
			return lcfirst($val);
		}, $matches[0]);
		$len = count($matches);

		if (!$isBlock) {
			return [
				'/' . implode('/', $matches) . '.latte',
				NULL
			];
		}

		return [
			'/'. ($len === 1 ? $matches[0] : implode('/', array_slice($matches, 0, $len - 1))) . '.latte',
			$len === 1 ? NULL : $matches[$len - 1]
		];
	}

}
