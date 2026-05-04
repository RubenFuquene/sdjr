@props(['url'])
<tr>
<td class="header">
<a href="{{ config('app.frontend_prod_url', $url) }}" style="display: inline-block;">
<img
    src="{{ asset('brand/isotipo-512-napa.png') }}"
    class="logo"
    alt="{{ config('mail.from.name', config('app.name')) }} Logo"
>
</a>
</td>
</tr>
