@php $s = $product->status; @endphp
@if($s === 'out')<span class="badge text-bg-danger">Out of stock</span>
@elseif($s === 'low')<span class="badge text-bg-warning">Low stock</span>
@else<span class="badge text-bg-success">In stock</span>@endif
