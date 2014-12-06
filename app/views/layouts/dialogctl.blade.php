@if (isset($dialog) && count($dialog))
	<div class="dialog">
	@foreach ($dialog as $index => $button)
		@if($index) / @endif {{(strpos($button['link'], 'javascript:') !== false) ? '<a href="' . $button['link'] . '">{$' . $button['title'] . '}</a>' : HTML::link($button['link'], '{$'.$button['title'].'}')}}
	@endforeach
	</div>
@endif
