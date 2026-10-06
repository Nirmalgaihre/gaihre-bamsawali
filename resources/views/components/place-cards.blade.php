@props(['places','admin'=>false])
<div class="place-grid">
@forelse($places as $place)
<article class="panel place-card">
 <div class="place-card-top"><span class="eyebrow">{{ $place->outline_number }} · स्थान</span>@if($admin)<span class="badge {{ $place->public_visible ? 'green' : '' }}">{{ $place->public_visible ? 'Visible' : 'Hidden' }}</span>@endif</div>
 <h2><a href="{{ $admin ? route('admin.subjects.show',$place) : $place->publicUrl() }}">{{ $place->title }}</a></h2>
 @if($place->location)<span class="place-label">{{ $place->location }}</span>@endif
 @if($place->description)<p>{{ Str::limit($place->description,190) }}</p>@endif
 <div class="place-card-links">@if($admin)<a class="text-link" href="{{ route('admin.subjects.edit',$place) }}">Edit place</a><a class="text-link" href="{{ route('admin.subjects.create',['parent_id'=>$place->id]) }}">+ Add section</a><a class="text-link" href="{{ route('admin.subjects.show',$place) }}">Manage genealogy →</a>@else<a class="text-link" href="{{ $place->publicUrl() }}">विवरण र वंशावली हेर्नुहोस् →</a>@endif</div>
</article>
@empty
<div class="empty-state panel wide"><h2>{{ $admin ? 'Add your first place' : 'स्थानका विवरणहरू तयार हुँदैछन्।' }}</h2><p>{{ $admin ? 'Create a separate place, add its description, and connect its starting ancestor. Add topics and sub-places below it.' : 'प्रकाशित स्थानहरू यहाँ छुट्टाछुट्टै देखिनेछन्।' }}</p>@if($admin)<a class="button" href="{{ route('admin.subjects.create',['type'=>'location']) }}">+ Add place</a>@endif</div>
@endforelse
</div>
