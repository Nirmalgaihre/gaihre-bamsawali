<ol class="contents-list">
@foreach($groups->get($parentId,collect()) as $item)
<li><a class="toc-row" href="{{ $item->publicUrl() }}"><span class="toc-number">{{ $item->outline_number }}</span><strong>{{ $item->title }}</strong><i aria-hidden="true"></i><span class="toc-page">{{ $item->book_page ?? '—' }}</span></a>@if($groups->has($item->id))@include('components.contents-list',['parentId'=>$item->id])@endif</li>
@endforeach
</ol>
