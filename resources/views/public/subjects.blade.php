@extends('layouts.public')
@section('title','विषय सूची')
@section('description','स्थान, विषय र उपविषयहरूको क्रमबद्ध सूची र वंशावली।')
@section('content')
<section class="page-heading"><span class="eyebrow">गैह्रे वंशावली • डिजिटल पुस्तक</span><h1>विषय सूची</h1><p>शीर्षकमा थिचेर त्यस स्थानको विवरण र वंशावली हेर्नुहोस्। पृष्ठ अङ्क मूल पुस्तकका सन्दर्भ हुन्।</p></section>
<section class="section subject-section"><div class="contents-paper"><div class="toc-row toc-heading"><span>क्र.सं.</span><strong>शीर्षक</strong><i></i><span>पृष्ठ</span></div>@if($groups->isNotEmpty())@include('components.contents-list',['parentId'=>0])@else<div class="empty-state"><h3>विषय सूची तयार हुँदैछ।</h3><p>स्थान र विषयसम्बन्धी विवरणहरू थपिएपछि यहाँ देखिनेछन्।</p></div>@endif</div>
@if($groups->isNotEmpty())<details class="outline-details"><summary>सबै विवरणहरू विस्तारमा हेर्नुहोस्</summary><div class="panel outline-panel">@include('components.subject-list',['parentId'=>0,'admin'=>false])</div></details>@endif</section>
@endsection
