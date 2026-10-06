@extends('layouts.public')
@section('title','लेख तथा संस्मरण')
@section('content')<section class="page-heading"><span class="eyebrow">कथा • इतिहास • परम्परा</span><h1>लेख तथा संस्मरण</h1><p>हाम्रा पुर्खाका सम्झना र पुस्ताहरूका कथा।</p></section><section class="section"><div class="posts-grid">@forelse($posts as $post)<x-post-card :post="$post" />@empty<div class="empty-state wide"><h2>लेखहरू चाँडै प्रकाशित हुनेछन्।</h2><p>हाम्रा कथा र ऐतिहासिक अभिलेखहरू संकलन हुँदैछन्।</p></div>@endforelse</div>{{ $posts->links() }}</section>@endsection
