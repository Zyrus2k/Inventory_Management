@props(['name', 'label', 'options', 'value' => null, 'required' => false, 'col' => 'col-md-6', 'placeholder' => '-- Select --'])
<div class="{{ $col }}">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" {{ $attributes }} @required($required)>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $k => $v)
            <option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
