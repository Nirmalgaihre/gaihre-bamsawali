@extends('layouts.admin')
@section('title',$entry->exists ? 'Edit '.$entry->title : ($entry->type === 'location' ? 'Add place / स्थान थप्नुहोस्' : 'Add विषय सूची entry'))
@section('content')
<form class="panel form-panel" method="post" action="{{ $entry->exists ? route('admin.subjects.update',$entry) : route('admin.subjects.store') }}">
 @csrf @if($entry->exists) @method('PUT') @endif
 <div class="form-intro"><h2>स्थान तथा विषय विवरण</h2><p>Select a parent to create a child entry. Numbering follows the hierarchy automatically.</p></div>
 <label for="title">Title / शीर्षक *</label><input id="title" name="title" value="{{ old('title',$entry->title) }}" maxlength="255" required>
 <div class="form-grid"><div><label for="type">Entry type *</label><select id="type" name="type">@foreach(\App\Models\SubjectEntry::TYPES as $value=>$label)<option value="{{ $value }}" @selected(old('type',$entry->type)===$value)>{{ $label }}</option>@endforeach</select></div>
 <div><label for="location">Location / स्थान (optional)</label><input id="location" name="location" value="{{ old('location',$entry->location) }}" maxlength="255" placeholder="District, village, or place name"></div></div>
 <label for="subject-parent-filter">Search parent</label><input id="subject-parent-filter" type="search" data-filter-select="parent_id" placeholder="Search an existing location or topic…">
 <label for="parent_id">Parent / अभिभावक</label><select id="parent_id" name="parent_id"><option value="">No parent — main entry</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected((string)old('parent_id',$entry->parent_id)===(string)$parent->id)>{{ $parent->title }} @if($parent->location) · {{ $parent->location }} @endif</option>@endforeach</select>
 <p class="field-hint">An entry and its descendants cannot be selected as its parent.</p>
 <h2 class="form-section-heading">Book reference & genealogy chart</h2>
 <label for="book_page">Original book page / पुस्तकको पृष्ठ</label><input id="book_page" name="book_page" type="number" min="1" max="1000000" value="{{ old('book_page',$entry->book_page) }}"><p class="field-hint">Optional reference to the printed book. This is separate from entry numbering and generation numbers.</p>
 <label for="ancestor-search">Search starting ancestor</label><input id="ancestor-search" type="search" data-filter-select="root_member_id" placeholder="Search Nepali or English name…">
 <label for="root_member_id">Starting ancestor / सुरुको पुर्खा</label><select id="root_member_id" name="root_member_id"><option value="">Text only — no genealogy chart</option>@foreach($ancestors as $ancestor)<option value="{{ $ancestor->id }}" @selected((string)old('root_member_id',$entry->root_member_id)===(string)$ancestor->id)>{{ $ancestor->name_ne }} / {{ $ancestor->name_en }} · Generation {{ $ancestor->generation }} @if(!$ancestor->status) · Hidden @endif</option>@endforeach</select>
 <p class="field-hint">Choose an existing member to show their entire branch on this entry’s public page. The same ancestor can appear in more than one book entry. Hidden ancestors and branches stay hidden publicly.</p>
 <a class="text-link" href="{{ route('admin.members.create') }}" target="_blank" rel="noopener">+ Create an ancestor in a new tab ↗</a><p class="field-hint">Save this entry first if you have unsaved changes. After creating an ancestor, reopen the entry to select them.</p>
 <label for="description">Description / विवरण</label><textarea id="description" name="description" rows="6" maxlength="10000">{{ old('description',$entry->description) }}</textarea>
 <label for="additional_notes">Additional notes / थप जानकारी</label><textarea id="additional_notes" name="additional_notes" rows="3" maxlength="5000">{{ old('additional_notes',$entry->additional_notes) }}</textarea>
 <div class="form-grid"><div><label for="display_order">Display order *</label><input id="display_order" type="number" name="display_order" value="{{ old('display_order',$entry->display_order) }}" min="0" max="1000000" required></div>
 <div><label for="status">Visibility *</label><select id="status" name="status"><option value="1" @selected(old('status',$entry->status)==1)>Visible</option><option value="0" @selected(old('status',$entry->status)==0)>Hidden</option></select></div></div>
 <p class="field-hint">Descriptions and additional notes are public when the entry and all its parents are visible.</p>
 <div class="form-actions"><button class="button">Save entry →</button><a class="button secondary" href="{{ route('admin.subjects.index') }}">Cancel</a></div>
</form>
@endsection
