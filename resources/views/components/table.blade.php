<style>
    .dataTables_filter {
        float: right;
    }
    .form-control-sm {
        border-radius: 0.2rem;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">

                    <div class="text-right">
                        <x-admin-button :table="$table" :entity="$entity" :action="'create'">
                            @lang('admin.add')
                        </x-admin-button>
                    </div>

                    <table id="table" class="table table-bordered table-hover text-center">
                        <thead>
                        <tr>
                            @foreach($columns as $col)
                                <th class="align-middle">@lang("admin.$col")</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            @foreach($columns as $col)
                                <th class="align-middle">@lang("admin.$col")</th>
                            @endforeach
                        </tr>
                        </tfoot>
                    </table>
                    <script>
                        let columns = JSON.parse('{!!json_encode($columns, JSON_UNESCAPED_UNICODE)!!}');
                        $(function () {
                            let dataColumns = [];
                            for (let i = 0; i < columns.length; i++)
                            {
                                let col = {"data": columns[i]};
                                if (columns[i] === 'edit' || columns[i] === 'delete')
                                    col['orderable'] = false;
                                dataColumns.push(col);
                            }
                            $('#table').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "ajax": "{!!route("$table.page")!!}",
                                "columns": dataColumns,
                                "paging": true,
                                "pageLength": 15,
                                "lengthMenu": [5, 15, 30, 50],
                                "lengthChange": true,
                                "info": true,
                                "autoWidth": false,
                                "responsive": true,
                                "language": {
                                    "paginate": {
                                        "previous": "@lang('pagination.previous')",
                                        "next": "@lang('pagination.next')",
                                    },
                                    "info": "@lang('admin.info')",
                                    "infoEmpty": "@lang('admin.info')",
                                    "emptyTable": "@lang('admin.empty_table')",
                                    "sZeroRecords": "@lang('admin.empty_search')",
                                    "search": "@lang('admin.search')",
                                    "lengthMenu": "@lang('admin.entries_count')",
                                    "infoFiltered":   "@lang('admin.filtered')",
                                }
                            });
                        });
                    </script>

                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
