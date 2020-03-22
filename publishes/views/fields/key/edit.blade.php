{!! $field->value() !!}
@if ($field->hiddenField())
    {!! Form::hidden($field->name(), $field->value(), $attributes) !!}
@endif
