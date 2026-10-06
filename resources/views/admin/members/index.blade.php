@extends('layouts.admin')
@section('title','वंशावली सदस्यहरू')
@section('actions')<a class="button" href="{{ route('admin.members.create') }}">+ Add member</a>@endsection
@section('content')
<div class="members-workspace">
 <div class="members-toolbar">
  <form class="members-simple-search" action="{{ route('admin.members.index') }}">
   <label class="sr-only" for="member-query">Search members</label><input id="member-query" name="q" value="{{ $query }}" placeholder="नाम खोज्नुहोस् / Search name…" maxlength="100">
   @if($branch)<input type="hidden" name="branch_id" value="{{ $branch->id }}">@endif
   <label class="sr-only" for="member-sort">View</label><select id="member-sort" name="sort"><option value="hierarchy" @selected($sort==='hierarchy')>Parent & children</option><option value="latest" @selected($sort==='latest')>Latest added</option></select>
   <button class="button secondary">Search</button>
  </form>
  <a class="text-link" href="{{ route('admin.members.tree') }}">Tree view ↗</a>
 </div>
 <div class="members-list-caption"><span>{{ $members->total() }} members @if($branch) · {{ $branch->name_ne }}@endif</span>@if($query || $branch || $sort==='latest')<a href="{{ route('admin.members.index') }}">Clear filters ×</a>@else<span>Parent → Child</span>@endif</div>
 @if($query && $sort==='hierarchy')<p class="members-search-note">Matching names are shown with their parents.</p>@endif
 <div class="table-scroll"><table class="members-simple-table"><thead><tr><th>क्र.सं.</th><th>नाम / Member</th><th>पुस्ता</th><th><span class="sr-only">Actions</span></th></tr></thead><tbody>
 @forelse($members as $member)
 <tr id="member-row-{{ $member->id }}" @class(['member-root-row'=>$member->parent_id===null && $sort==='hierarchy'])>
  <td><span class="members-number">{{ $member->serial_number }}</span></td>
  <td><div class="members-name" style="--member-indent: {{ $sort==='hierarchy' ? min($member->outline_depth - ($branch?->outline_depth ?? 0), 5) * 18 : 0 }}px">
   @if($member->parent_id && $sort==='hierarchy')<span class="members-child-line" aria-hidden="true">└</span>@else<span class="members-initial" aria-hidden="true">{{ mb_substr($member->name_ne,0,1) }}</span>@endif
   <div><a class="members-title" href="{{ route('admin.members.edit',$member) }}">{{ $member->name_ne }}</a>
    <div class="members-meta">@if($member->name_en)<span>{{ $member->name_en }}</span>@endif
    @if($member->parent)<a href="{{ route('admin.members.index',['branch_id'=>$member->parent_id]) }}">Parent: {{ $member->parent->name_ne }}</a>@else<span>Root ancestor</span>@endif
    @if($member->children_count)<a href="{{ route('admin.members.index',['branch_id'=>$member->id]) }}">{{ $member->children_count }} children ↗</a>@endif
    @if(!$member->public_visible)<span class="members-hidden">{{ $member->status ? 'Hidden by parent' : 'Hidden' }}</span>@endif
    @if($sort==='latest')<time datetime="{{ $member->created_at->toIso8601String() }}">{{ $member->created_at->format('d M Y') }}</time>@endif</div>
   </div></div></td>
  <td><span class="members-generation">{{ $member->generation }}</span></td>
  <td><div class="members-row-actions"><a href="{{ route('admin.members.edit',$member) }}">Edit</a><a class="members-add-child" href="{{ route('admin.members.create',['parent_id'=>$member->id]) }}">+ Child</a></div></td>
 </tr>
 @empty<tr><td colspan="4"><div class="empty-state"><span class="empty-symbol">♧</span><h3>{{ $query ? 'No matching members' : 'आफ्नो पहिलो पुर्खा थप्नुहोस्' }}</h3><p>{{ $query ? 'Try another Nepali or English name.' : 'Start with a parent, then add children to grow the family.' }}</p><a class="button secondary" href="{{ route('admin.members.create') }}">+ Add member</a></div></td></tr>@endforelse
 </tbody></table></div>
</div>
<div class="members-pagination">{{ $members->links() }}</div>
@endsection
