<!DOCTYPE html>
<html lang="ne"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', $settings['site_name_ne']) · {{ $settings['site_name'] }}</title>
<meta name="description" content="@yield('description', $settings['tagline'])"><link rel="canonical" href="{{ url()->current() }}">
<meta property="og:title" content="@yield('title', $settings['site_name_ne'])"><meta property="og:description" content="@yield('description', $settings['tagline'])"><meta property="og:url" content="{{ url()->current() }}"><meta property="og:type" content="@yield('og_type','website')"><meta property="og:locale" content="ne_NP">@yield('meta')
@vite(['resources/css/app.css','resources/js/app.js'])</head><body>
<div class="heritage-strip"><span>{{ $settings['tagline'] }}</span><span>पुस्ताहरू जोड्ने साझा अभिलेख</span></div>
<header class="site-header"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">गै</span><span><strong>{{ $settings['site_name_ne'] }}</strong><small>{{ $settings['site_name'] }}</small></span></a>
<button class="menu-toggle button secondary" aria-expanded="false" aria-controls="main-nav">☰ <span>मेनु</span></button>
<nav id="main-nav" aria-label="मुख्य नेभिगेसन">@foreach(['home'=>'गृहपृष्ठ','bansawali'=>'वंशावली','places'=>'स्थानहरू','subjects'=>'विषय सूची','gaihre-parichaya'=>'गैह्रे परिचय','history'=>'इतिहास','blog'=>'लेखहरू','about'=>'हाम्रो बारेमा'] as $route=>$label)<a @class(['active'=>request()->routeIs($route) || ($route==='blog' && request()->routeIs('blog.*'))]) href="{{ route($route) }}">{{ $label }}</a>@endforeach<a class="nav-contact" href="{{ route('contact') }}">सम्पर्क ↗</a></nav></header>
<main id="main-content">@yield('content')</main>
<footer class="site-footer"><div class="footer-top"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">गै</span><span><strong>{{ $settings['site_name_ne'] }}</strong><small>{{ $settings['site_name'] }}</small></span></a><p>{{ $settings['footer_text'] }}</p><a href="{{ route('bansawali') }}">वंशावली हेर्नुहोस् ↗</a></div><div class="footer-bottom"><span>© {{ date('Y') }} {{ $settings['site_name'] }}</span><div><a href="{{ route('contact') }}">सम्पर्क</a><a href="{{ route('login') }}">व्यवस्थापक प्रवेश</a></div></div></footer>
</body></html>
