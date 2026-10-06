@extends('layouts.public')
@section('title',$page->title)
@section('description',Str::limit(strip_tags($page->content),160))
@section('content')<section class="page-heading"><span class="eyebrow">गैह्रे वंशावली / {{ $page->title }}</span><h1>{{ $page->title }}</h1><span class="heading-ornament">✧</span></section><article class="reading-column prose">{!! \App\Services\SafeHtml::clean($page->content) !!}@if($page->key==='contact')<div class="contact-note">{{ $settings['contact_text'] }}</div>@endif</article><div class="reading-footer"><a class="text-link" href="{{ route('bansawali') }}">हाम्रो वंशावली हेर्नुहोस् ↗</a></div>@endsection
