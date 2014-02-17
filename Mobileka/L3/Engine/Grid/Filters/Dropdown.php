<?php namespace Mobileka\L3\Engine\Grid\Filters;

use Mobileka\L3\Engine\Laravel\Lang;

class Dropdown extends BaseComponent {

	protected $template = 'engine::grid.filters.dropdown';

	public function value($lang = '')
	{
		return \Arr::searchRecursively($this->filters, 'where', $this->name);
	}

	public function options($options, $defaultValue = true)
	{
		if ($defaultValue)
		{
			$options = array(null => Lang::findLine($this->languageFile, 'not_selected')) + $options;
		}

		$this->options = $options;

		return $this;
	}

}