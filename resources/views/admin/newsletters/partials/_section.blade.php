{{-- How the platform admin's three newsletter pages open, under the admin navigation:
     Newsletters, Segments and Templates as tabs, where the index had two bordered buttons and
     the other two a "Back" button (and Templates had no navigation at all).

     The page's name is the admin navigation's to say, so there is no title row here: the tabs,
     then what the last request said. Expects $tab: newsletters, segments or templates.

     `strip`: a phone keeps these three as a strip. The admin navigation above is a dropdown
     there, and a second dropdown under it read the same word, "Newsletters". --}}
<x-page-tabs id="admin-newsletter-tabs" strip :label="__('messages.admin_newsletters')" :tabs="[
    ['label' => __('messages.admin_newsletters'), 'href' => route('admin.newsletters.index'), 'current' => $tab === 'newsletters'],
    ['label' => __('messages.segments'), 'href' => route('admin.newsletters.segments'), 'current' => $tab === 'segments'],
    ['label' => __('messages.templates'), 'href' => route('admin.newsletters.templates'), 'current' => $tab === 'templates'],
]" />

@include('newsletter.partials._notices')
