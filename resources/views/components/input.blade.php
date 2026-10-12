@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false, 'col' => 'col-md-6'])
<div class="{{ $col }}">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           class="form-control @error($name) is-invalid @enderror" {{ $attributes }} @required($required)>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
