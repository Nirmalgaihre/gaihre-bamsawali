@extends('layouts.admin')
@section('title','स्थानहरू · Separate place sections')
@section('actions')<a class="button" href="{{ route('admin.subjects.create',['type'=>'location']) }}">+ Add place</a>@endsection
@section('content')
<div class="welcome-panel"><div><h2>One place, one dedicated page.</h2><p>Add a place → write its description → choose its starting ancestor → add child sections.</p><p>Each published place appears on the frontend with its own topics and genealogy branch.</p></div><a class="button secondary" href="{{ route('places') }}" target="_blank" rel="noopener">View places ↗</a></div>
<x-place-cards :places="$places" :admin="true" />
@endsection
