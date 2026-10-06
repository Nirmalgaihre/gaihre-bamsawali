@extends('layouts.public')
@section('title',$entry->title)
@section('description',Str::limit($entry->description ?: $entry->title,160))
@section('content')
<section class="page-heading book-heading"><nav class="book-breadcrumb" aria-label="स्थानक्रम"><a href="{{ route('places') }}">स्थानहरू</a><span> / </span><a href="{{ route('subjects') }}">विषय सूची</a>@foreach($breadcrumbs as $crumb)<span> / </span><a href="{{ $crumb->publicUrl() }}">{{ $crumb->title }}</a>@endforeach</nav><span class="eyebrow">{{ $entry->outline_number }} · {{ \App\Models\SubjectEntry::TYPES[$entry->type] }}</span><h1>{{ $entry->title }}</h1>@if($entry->location)<p>{{ $entry->location }}</p>@endif @if($entry->book_page)<span class="book-page-badge">पुस्तक पृष्ठ {{ $entry->book_page }}</span>@endif</section>
<section class="section book-section">
@if($entry->description)<div class="book-description">{{ $entry->description }}</div>@endif
@if($entry->additional_notes)<details class="subject-notes book-notes"><summary>थप जानकारी</summary><p>{{ $entry->additional_notes }}</p></details>@endif
@if($entry->root_member_id)
 <form class="search-form" action="{{ $entry->publicUrl() }}"><label class="sr-only" for="branch-search">सदस्य खोज्नुहोस्</label><input id="branch-search" name="q" value="{{ $query }}" maxlength="100" placeholder="यस शाखामा सदस्य खोज्नुहोस्…"><button class="button">खोज्नुहोस्</button>@if($query)<a class="button secondary" href="{{ $entry->publicUrl() }}">रिसेट</a>@endif</form>
 @if($query)<div class="search-results">@forelse($results as $result)<a class="search-result" href="#member-{{ $result->id }}" data-focus-member="member-{{ $result->id }}"><strong>{{ $result->name_ne }}</strong><span>{{ $result->generation }} पुस्ता · वंशावलीमा हेर्नुहोस् ↗</span></a>@empty<p>यो शाखामा सदस्य भेटिएन।</p>@endforelse</div>@endif
 <x-tree :members="$members" :groups="$groups" :book="true" />
 <p class="field-hint">नामसँगको अङ्कले पुस्ता जनाउँछ। स्थानहरू नाममुनि देखाइएका छन्।</p>
@endif
@if($children->isNotEmpty())<div class="book-child-pages"><h2>सम्बन्धित उपविषयहरू</h2>@foreach($children as $child)<a class="toc-row" href="{{ $child->publicUrl() }}"><span>{{ $child->outline_number }}</span><strong>{{ $child->title }}</strong><i></i><span>{{ $child->book_page ?? '→' }}</span></a>@endforeach</div>@endif
<div class="reading-footer"><a class="text-link" href="{{ route('subjects') }}">← विषय सूचीमा फर्कनुहोस्</a></div>
</section>
@endsection
