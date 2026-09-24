{{--
    This page, as a schema.org WebPage: what it is called, who it is for, and - by @id - which
    product it is about (the layout's one SoftwareApplication, {site}/#software), which site it is
    part of and who publishes it. A marketing page describes itself here rather than restating the
    product; see SeoUtils::softwareApplication() for why.

    Props:
      name        - the page's subject, e.g. "Event Schedule for Bars and Pubs"
      description - one or two sentences on what the page covers
      audience    - who it is for, as schema.org Audience.audienceType
      keywords    - comma-separated, as the page's own keyword list
      mentions    - competitors the page compares against, as schema.org Brand (an array of names)
--}}
@props([
    'name' => null,
    'description' => null,
    'audience' => null,
    'keywords' => null,
    'mentions' => [],
])
<script type="application/ld+json" {!! nonce_attr() !!}>
{!! \App\Utils\SeoUtils::jsonLd(\App\Utils\SeoUtils::webPage($name, $description, $audience, $keywords, (array) $mentions)) !!}
</script>
