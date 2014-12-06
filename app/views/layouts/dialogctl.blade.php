@if (isset($dialog) && count($dialog))
	<div class="dialog">
	@foreach ($dialog as $index => $button)
		@if($index) / @endif {{HTML::link($button['link'], '{$'.$button['title'].'}')}}
	@endforeach
	</div>
@endif
