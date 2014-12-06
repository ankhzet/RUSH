@extends('layouts.structure')

@section('content')
<h4>Ужасный Джерик Фосби</h4>
<p class="cstat">Здоровье: <span>10K/10K</span></p>
<p class="cstat">Мана: <span>50K/50K</span></p>
<p>Уровень: 50 <span style="color: gray; float: right;">[Альянс]</span></p>
<hr><p>Привет, AnkhZet.</p>

<h4>Спасти кролика!</h4>

<p>Привет, AnkhZet.<br />
Мой миленький маленький кролик потерялся, можешь помочь найти его?</p>
<h4>Цели задания:</h4>

<ul>
	<li>Убить: Вестник смерти  (0/12)</li>
</ul>

<h4>Вы получите одну из наград:</h4>

<div class="items">
	<div class="item"><a><img src="/theme/img/icons/ui/10.png"></a></div>
	<div class="desc">
		<h4 class="rare4">Сумка путешественника</h4>
		<h5><p>&nbsp;<a style="font-size: 50%;" href="/items/guid/3">Подробнее...</a></p></h5>
	</div>
</div>
<div class="items">
	<div class="item"><a><img src="/theme/img/icons/ui/11.png"></a></div>
	<div class="desc">
		<h4 class="rare1">Небольшая сумка</h4>
		<h5><p>&nbsp;<a style="font-size: 50%;" href="/items/guid/2">Подробнее...</a></p></h5>
	</div>
</div>

<h4>Вы также получите:</h4>

<p class="cstat">Деньги: <span class="money"><span class="copper">3</span></span></p>

<div class="items">
	<div class="item"><a><img src="/theme/img/icons/ui/1.png"></a></div>
	<div class="desc">
		<h4 class="rare2">Жетон "За отвагу"</h4>
		<h5><p>&nbsp;<a style="font-size: 50%;" href="/items/guid/1">Подробнее...</a></p>Награда за победу в битве.</h5>
	</div>
</div>

<div class="dialog"><a href="/npc">&lt;&lt; Назад</a> / <a href="/quest/decline">Отказаться</a></div>
@stop
