<tr>
    <td colspan="2">
        @if (\Terranet\Administrator\Field\HasMany::MODE_MULTI_SELECT === $editMode)
            <select id="{{ $field->name() }}" name="{{ $field->name() }}" multiple>
                @foreach($values as $related)
                    <option value="{{ $related->getKey() }}" {!! $field->value()->contains($related) ? 'selected' : '' !!}>
                        {{ $related->{$titleField} }}
                    </option>
                @endforeach
            </select>
        @elseif (\Terranet\Administrator\Field\HasMany::MODE_TAGS === $editMode)
            <tag-list :items="{{ $field->value() }}"
                      name="{{ $field->name() }}"
                      key-name="{{ $relation->getRelated()->getKeyName() }}"
                      label-name="{{ $titleField }}"
                      search-url="{{ route('scaffold.search', ['searchable' => $searchable, 'field' => $titleField]) }}"
            ></tag-list>
        @elseif(\Terranet\Administrator\Field\HasMany::MODE_CHECKBOXES === $editMode)
            <ul class="list-unstyled">
                @foreach($values as $related)
                    <li style="width: 200px; display: inline-block">
                        <label>
                            <input type="checkbox"
                                   name="{{ $field->name() }}[]"
                                   value="{{ $related->getKey() }}" {!! $field->value()->contains($related) ? 'checked="checked"': '' !!}>
                            {{ $related->{$titleField} }}
                        </label>
                    </li>
                @endforeach
            </ul>
        @elseif(\Terranet\Administrator\Field\HasMany::MODE_CREATE_FORM === $editMode)
            @foreach($columnsCollection as $columns)
                @foreach($columns as $field)
                    @include('administrator::edit.row', ['field' => $field])
                @endforeach
            @endforeach
        @endif
    </td>
</tr>
