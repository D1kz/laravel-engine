{{ Form::select($component->name, $component->options, Input::old($component->name, $component->value($lang)), $component->attributes) }}