<?php

class ComponentMacroTest extends \Codeception\Test\Unit {

	/**  @var \UnitTester */
	protected $tester;
	
	/** @var \Latte\Engine */
	protected $engine;

	/**
	 * @return \Latte\Engine
	 */
	private function createEngine() {
		$engine = new \Latte\Engine();
		\WebChemistry\Macros\ComponentMacro::install($engine->getCompiler(), __DIR__ . '/../_data/templates');

		return $engine;
	}

	protected function _before() {
		$this->engine = $this->createEngine();
	}

	// tests
	public function testTemplate() {
		$this->assertSame('Template success', $this->trim($this->render('template.latte')));
	}

	// tests
	public function testBlocks() {
		$code = $this->renderCode('blocks.latte');
		$this->assertSame(1, substr_count($code, '/../_data/templates/blocks.latte'));

		$render = $this->render('blocks.latte', $this->createEngine());
		$this->assertSame('Block 1 successBlock 2 success', $this->trim($render));
	}

	public function testWithDirectory() {
		$this->assertSame('Block 3 success', $this->trim($this->render('dir.latte')));
	}
	
	public function testRenderContentWithParams() {
		$this->assertSame('param  content', $this->trim($this->render('content.latte')));
	}
	
	public function testComplex() {
		$render = $this->render('complex.latte');

		$this->assertSame('var  content var  block block custom', $this->trim($render));
	}

	public function testModifiers() {
		$render = $this->render('modifiers.latte');

		$this->assertSame('Lorem Ipsum is...', $render);
	}

	public function testModifiersBlock() {
		$render = $this->render('modifiersBlock.latte');

		$this->assertSame('Lorem Ipsum is...', $render);
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function trim($str) {
		return str_replace(["\n", "\t"], '', $str);
	}

	/**
	 * @param string $template
	 * @param \Latte\Engine $engine
	 * @return string
	 */
	private function render($template, $engine = NULL) {
		$engine = $engine ? $engine : $this->engine;

		return $engine->renderToString(__DIR__ . '/templates/' .$template);
	}

	/**
	 * @param string $template
	 * @param \Latte\Engine $engine
	 * @return string
	 */
	private function renderCode($template, $engine = NULL) {
		$engine = $engine ? $engine : $this->engine;

		return $engine->compile(__DIR__ . '/templates/' .$template);
	}

}
