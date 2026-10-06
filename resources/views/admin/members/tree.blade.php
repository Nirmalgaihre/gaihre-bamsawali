@extends('layouts.admin')
@section('title','वंशावली · Tree view')
@section('actions')<a class="button" href="{{ route('admin.members.create') }}">+ Add member</a>@endsection
@section('content')<x-tree :members="$members" :groups="$groups" :admin="true" />@endsection
