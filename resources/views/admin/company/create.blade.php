<x-admin-layout>

    <x-slot name="prev">{{route('companies.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Companies')}}</x-slot>
    <x-slot name="header">
        @lang('admin.add') @lang('admin.company_genitive')
    </x-slot>

    <x-form :action="route('companies.store')"
            :method="'post'"
            :controls="$controls"
            :btnText="__('admin.add')"/>

</x-admin-layout>
