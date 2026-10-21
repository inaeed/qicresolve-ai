@props(['tone' => 'info'])
@php($safeTone = in_array($tone, ['info', 'warning', 'success', 'danger', 'neutral'], true) ? $tone : 'info')
<span {{ $attributes->class(['qic-badge', 'qic-badge-'.$safeTone]) }}>{{ $slot }}</span>
