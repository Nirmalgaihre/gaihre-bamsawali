@extends('layouts.public')
@section('title',$post->meta_title ?: $post->title)
@section('description',$post->meta_description ?: Str::limit($post->short_description ?: strip_tags($post->content),160))
@section('og_type','article')
@section('meta')@if($post->featured_image)<meta property="og:image" content="{{ url(Storage::url($post->featured_image)) }}">@endif @endsection
@section('content')<section class="page-heading"><a class="eyebrow" href="{{ route('blog') }}">← लेखहरूमा फर्कनुहोस्</a><h1>{{ $post->title }}</h1><p>{{ $post->published_at->format('Y.m.d') }} · {{ $post->author->name }}</p></section><article class="reading-column">@if($post->featured_image)<img class="article-cover" src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}">@endif<div class="prose">{!! \App\Services\SafeHtml::clean($post->content) !!}</div></article>@if($related->isNotEmpty())<section class="section"><div class="section-heading"><h2>थप लेखहरू</h2></div><div class="posts-grid">@foreach($related as $item)<x-post-card :post="$item" />@endforeach</div></section>@endif @endsection
