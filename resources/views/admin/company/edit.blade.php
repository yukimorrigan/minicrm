<x-admin-layout>

    <x-slot name="prev">{{route('companies.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Companies')}}</x-slot>
    <x-slot name="header">
        @lang('admin.edit') @lang('admin.company_genitive')
    </x-slot>

    <x-form :action="route('companies.update', ['company' => $id])"
            :method="'put'"
            :controls="$controls"
            :btnText="__('admin.edit')"/>

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>
