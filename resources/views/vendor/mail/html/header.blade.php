@props(['url', 'logo' => null])
<tr>
<td class="header">
<a href="{{ $url }}" class="brand" style="display: inline-block;">
<table class="brand-table" cellpadding="0" cellspacing="0" role="presentation" align="center">
<tr>
@if ($logo)
<td class="brand-logo-cell"><img src="{{ $logo }}" class="logo" width="40" height="40" alt=""></td>
@endif
<td class="brand-name">Estate<span class="brand-accent">Flow</span></td>
</tr>
</table>
</a>
</td>
</tr>
