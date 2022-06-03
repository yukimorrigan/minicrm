@props(['active' => false])

<li class="nav-item">
    <a {{ $attributes->merge(['class' => 'nav-link' . ($active ? ' active' : '')]) }}>
        <i class="far fa-circle nav-icon"></i>
        <p>{{$slot}}</p>
    </a>
</li>
