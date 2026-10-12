@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false, 'col' => 'col-12'])
<div class="{{ $col }}">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              class="form-control @error($name) is-invalid @enderror" @required($required)>{{ old($name, $value) }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
