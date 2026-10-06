@extends('layouts.public')
@section('title',$page?->title ?? 'स्थानअनुसार वंशावली')
@section('content')
<section class="page-heading"><span class="eyebrow">हाम्रा स्थान • हाम्रा शाखा</span><h1>{{ $page?->title ?? 'स्थानअनुसार वंशावली' }}</h1>@if($page)<div class="prose">{!! \App\Services\SafeHtml::clean($page->content) !!}</div>@endif</section>
<section class="section place-section"><x-place-cards :places="$places" /></section>
@endsection
