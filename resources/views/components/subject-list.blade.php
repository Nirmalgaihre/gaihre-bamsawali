<ol class="subject-list">
@forelse($groups->get($parentId,collect()) as $item)
 <li id="subject-{{ $item->id }}">
  <div class="subject-entry">
   <span class="subject-number">{{ $item->outline_number }}</span>
   <div class="subject-body">
    <div class="subject-heading"><h2><a href="{{ $admin ? route('admin.subjects.show',$item) : $item->publicUrl() }}">{{ $item->title }} ↗</a></h2><span class="badge">{{ \App\Models\SubjectEntry::TYPES[$item->type] }}</span>@if($admin && !$item->public_visible)<span class="badge">{{ $item->status ? 'Hidden by parent' : 'Hidden' }}</span>@endif</div>
    @if($item->book_page)<span class="badge">पुस्तक पृष्ठ {{ $item->book_page }}</span>@endif
    @if($item->location)<p class="subject-location">स्थान: {{ $item->location }}</p>@endif
    @if($item->description)<p class="subject-description">{{ $item->description }}</p>@endif
    @if($item->additional_notes)<details class="subject-notes"><summary>थप जानकारी / Additional notes</summary><p>{{ $item->additional_notes }}</p></details>@endif
    @if($admin)<div class="table-actions subject-actions"><a href="{{ route('admin.subjects.edit',$item) }}">Edit</a><a href="{{ route('admin.subjects.show',$item) }}">Manage chart</a><a href="{{ route('admin.subjects.create',['parent_id'=>$item->id]) }}">+ Add child</a><form method="post" action="{{ route('admin.subjects.destroy',$item) }}" data-confirm="Delete this entry? This cannot be undone.">@csrf @method('DELETE')<button class="text-danger" @disabled($groups->has($item->id)) title="{{ $groups->has($item->id) ? 'Move or delete child entries first' : 'Delete entry' }}">Delete</button></form></div>@endif
   </div>
  </div>
  @if($groups->has($item->id))@include('components.subject-list',['parentId'=>$item->id,'groups'=>$groups,'admin'=>$admin])@endif
 </li>
@empty
 <li class="empty-state"><h3>{{ $admin ? 'Start your विषय सूची' : 'विषय सूची तयार हुँदैछ।' }}</h3><p>{{ $admin ? 'Add a main location or topic, then add child entries beneath it.' : 'स्थान र विषयसम्बन्धी विवरणहरू थपिएपछि यहाँ देखिनेछन्।' }}</p></li>
@endforelse
</ol>
