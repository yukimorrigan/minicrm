<x-admin-layout>

    <x-slot name="prev">{{route('employees.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Employees')}}</x-slot>
    <x-slot name="header">
        @lang('admin.add') @lang('admin.employee_genitive')
    </x-slot>

    <x-form :action="route('employees.store')"
            :method="'post'"
            :controls="$controls"
            :btnText="__('admin.add')"/>

</x-admin-layout>
