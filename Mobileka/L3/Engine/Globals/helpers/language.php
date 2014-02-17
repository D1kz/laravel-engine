<?php

use \Mobileka\L3\Engine\Laravel\Lang;

function langs()
{
	return array(Config::get('application.language', 'en')) + Config::get('application.languages', array());
}

function gridLang($file, $word, $replacements = array(), $language = null)
{
	return crudLang('grid', $file, $word, $replacements = array(), $language = null);
}

function formLang($file, $word, $replacements = array(), $language = null)
{
	return crudLang('form', $file, $word, $replacements = array(), $language = null);
}

function filterLang($file, $word, $replacements = array(), $language = null)
{
	return crudLang('filters', $file, $word, $replacements = array(), $language = null);
}

function crudLang($module, $file, $word, $replacements = array(), $language = null)
{
	//сначала посмотреть в bundle::file.grid.word
	$key = Lang::key($file . '.' . $module, $word);
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation)
	{
		return $translation;
	}

	//если не нашлось, то в bundle::file.labels.word
	$key = Lang::key($file . '.labels', $word);
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation)
	{
		return $translation;
	}

	//если не нашлось, то в file.grid.word
	$key = $file . '.' . $module . '.' . $word;
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation)
	{
		return $translation;
	}

	//если не нашлось, то в file.labels.word
	$key = $file . '.labels.' . $word;
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation or $file === 'default')
	{
		return $translation;
	}

	//если не нашлось, то в default.grid.word
	$key = 'default.' . $module . '.' . $word;
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation)
	{
		return $translation;
	}

	//если не нашлось, то в default.labels.word
	$key = 'default.labels.' . $word;
	$translation = Lang::line($key, $replacements, $language)->get();

	if ($key !== $translation)
	{
		return $translation;
	}

	//если ничего не нашлось, то вернуть, что искалось
	return $file . '.' . $module . '.' . $word;
}

function ___($file, $word, $replacements = array(), $language = null)
{
	return Lang::findLine($file, $word, $replacements = array(), $language = null);
}