@extends('layouts.admin')
@section('title','विषय सूची · Locations & topics')
@section('actions')<a class="button" href="{{ route('admin.subjects.create') }}">+ Add entry</a>@endsection
@section('content')
<p class="page-description">Organize locations, topics, and sections into a numbered outline. Add a child under any entry, or edit its parent and display order to move it.</p>
<p class="field-hint">Lower display orders appear first. Numbers are calculated automatically. A hidden parent hides its entire public branch; public numbering skips hidden entries.</p>
<div class="panel outline-panel">@include('components.subject-list',['parentId'=>0,'admin'=>true])</div>
@endsection
