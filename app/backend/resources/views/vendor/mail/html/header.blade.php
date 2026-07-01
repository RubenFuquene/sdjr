@props(['url'])
@php($brandBaseUrl = rtrim(config('app.frontend_prod_url', config('app.url')), '/'))
<tr>
<td class="header">
<a href="{{ $brandBaseUrl }}" style="display: inline-block;">
<img
    src="{{ $brandBaseUrl.'/brand/isotipo-512-napa.png' }}"
    class="logo"
    alt="{{ config('mail.from.name', config('app.name')) }}"
>
</a>
</td>
</tr>
