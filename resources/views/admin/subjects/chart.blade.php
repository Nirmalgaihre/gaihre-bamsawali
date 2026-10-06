@extends('layouts.admin')
@section('title',$entry->title.' · Genealogy chart')
@section('actions')<a class="button secondary" href="{{ route('admin.subjects.edit',$entry) }}">Edit entry</a>@if($entry->root_member_id)<a class="button" href="{{ route('admin.members.create',['parent_id'=>$entry->root_member_id]) }}">+ Add child</a>@endif @endsection
@section('content')
<div class="place-management-actions"><a class="button secondary" href="{{ route('admin.subjects.create',['parent_id'=>$entry->id]) }}">+ Add child section</a><a class="button secondary" href="{{ route('admin.subjects.create',['parent_id'=>$entry->id,'type'=>'location']) }}">+ Add sub-place</a><a class="text-link" href="{{ $entry->publicUrl() }}" target="_blank" rel="noopener">View public page ↗</a></div>
@if($entry->description)<p class="book-description">{{ $entry->description }}</p>@endif
@if($children->isNotEmpty())<section class="panel place-child-list"><h2>Sections in this place</h2>@foreach($children as $child)<div class="recent-row"><a href="{{ route('admin.subjects.show',$child) }}">{{ $child->title }}</a><div class="table-actions"><a href="{{ route('admin.subjects.edit',$child) }}">Edit</a><a href="{{ route('admin.subjects.create',['parent_id'=>$child->id]) }}">+ Child</a></div></div>@endforeach</section>@endif

<p class="page-description">{{ $entry->location }} @if($entry->book_page) · Book page {{ $entry->book_page }} @endif</p>
@if(!$entry->root_member_id)<div class="empty-state panel"><h2>Choose a starting ancestor</h2><p>Edit this entry and select its starting ancestor to connect a family branch.</p><a class="button" href="{{ route('admin.subjects.edit',$entry) }}">Connect genealogy →</a></div>
@else<x-tree :members="$members" :groups="$groups" :admin="true" :book="true" />@endif
@endsection
