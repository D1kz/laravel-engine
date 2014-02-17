<?php
use Mobileka\L3\Engine\Form\Components\TextArea;

$attributes = $component->attributes;
$attributes['class'] = ($class = Arr::getItem($component->attributes, 'class')) ? $class . ' ckeditor' : 'ckeditor';
?>

{{ TextArea::make($component->name, $attributes)->localized($component->localized)->row($component->row)->render($lang) }}