<x-admin-layout>

    <x-slot name="prev">{{route('companies.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Companies')}}</x-slot>
    <x-slot name="header">
        @lang('admin.'.$action) @lang('admin.company_genitive')
    </x-slot>

    <x-form :action="$route"
            :method="$method"
            :controls="$controls"
            :btnText="__('admin.'.$action)"/>

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>
