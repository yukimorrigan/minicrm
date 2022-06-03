<x-admin-layout>

    <x-slot name="prev">{{route('companies.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Companies')}}</x-slot>
    <x-slot name="header">
        {{__('Companies')}}
    </x-slot>

    <x-table
        :table="'companies'"
        :entity="'company'"
        :columns="['id', 'company_name', 'email', 'phone', 'website', 'edit', 'delete']"
    />

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>
