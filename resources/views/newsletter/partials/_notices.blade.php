{{-- What the last request said, as the kit's one notice (x-page-notice). Each of these pages
     carried its own copy of three hand-built panels. Shared by the schedule owner's pages and the
     platform admin's. What a form got wrong is not here: layouts/app-admin lists that above
     every page of the portal, and a second list here said each message twice. --}}
<x-page-flash :keys="['status' => 'success', 'error' => 'error']" class="news-notice" />
