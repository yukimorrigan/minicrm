<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Http\Traits\TableHelper;
use App\Mail\NewEmployee;
use App\Models\Company;
use App\Models\Employee;
use App\Http\Requests\Crm\Employee\StoreEmployeeRequest;
use App\Http\Requests\Crm\Employee\UpdateEmployeeRequest;
use App\Models\User;
use App\View\Components\AdminButton;
use App\View\Components\ModalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class EmployeeController extends Controller
{
    use TableHelper;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.employee.index');
    }

    /**
     * AJAX pagination.
     *
     * @param PageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function page(PageRequest $request)
    {
        $page = (int) $request->start > 1 ? ($request->start / $request->length) + 1 : 1;
        $columnIndex = $request->order[0]['column']; // Column index
        $columnName = $request->columns[$columnIndex]['data']; // Column name
        $columnSortOrder = $request->order[0]['dir'] ?? 'asc'; // asc or desc
        $searchValue = $request->search['value']; // Search value

        $lang = config('app.locale');
        if ($columnName === 'first_name' || $columnName === 'last_name' )
            $columnName = "cast($columnName->>'$.$lang' as char)";

        // Fetch records
        $records = Employee::with('company')
            ->orderByRaw("$columnName $columnSortOrder")
            ->when($searchValue, fn($q) => $q
                ->where('employees.first_name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.first_name->ua', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.last_name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.last_name->ua', 'like', '%' .$searchValue . '%')
                ->orWhereHas('company', fn ($q) => $q
                    ->where('name->en', 'like', '%' .$searchValue . '%')
                    ->orWhere('name->ua', 'like', '%' .$searchValue . '%')
                )
            )
            ->paginate($request->length, ['*'], 'page', $page);

        $data = [];
        foreach($records as $record)
        {
            $editButton = new AdminButton('employees', 'employee', 'edit', $record['id']);
            $deleteButton = new AdminButton('employees', 'employee', 'destroy', $record['id'], true);
            $data[] = [
                "id"            => $record['id'],
                "company_name"  => $record->company()->first()->name,
                "first_name"    => $record["first_name"],
                "last_name"     => $record["last_name"],
                "email"         => $record['email'],
                "phone"         => $record['phone'],
                "edit"          => $editButton->render()->render(),
                "delete"        => $deleteButton->render()->render()
            ];
        }

        // Total records
        $totalRecords = Employee::select('count(*) as allcount')->count();
        $totalFilteredRecords = Employee::select("count(*) as allcount")
            ->with('company')
            ->when($searchValue, fn($q) => $q
                ->where('employees.first_name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.first_name->ua', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.last_name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('employees.last_name->ua', 'like', '%' .$searchValue . '%')
                ->orWhereHas('company', fn ($q) => $q
                    ->where('name->en', 'like', '%' .$searchValue . '%')
                    ->orWhere('name->ua', 'like', '%' .$searchValue . '%')
                )
            )
            ->count();

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRecords,
            "data" => $data
        ]);
    }

    public function controls()
    {
        return [
            [
                'type' => 'text',
                'name' => 'first_name_ua',
                'placeholder' => __('admin.employee_name_ua')
            ],
            [
                'type' => 'text',
                'name' => 'last_name_ua',
                'placeholder' => __('admin.employee_lastname_ua')
            ],
            [
                'type' => 'text',
                'name' => 'first_name_en',
                'placeholder' => __('admin.employee_name_en')
            ],
            [
                'type' => 'text',
                'name' => 'last_name_en',
                'placeholder' => __('admin.employee_lastname_en')
            ],
            [
                'type' => 'select',
                'name' => 'company_id',
                'placeholder' => __('admin.company_name'),
                'values' => Company::all()->pluck( 'name', 'id'),
                'value' => ''
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'placeholder' => __('admin.email')
            ],
            [
                'type' => 'text',
                'name' => 'phone',
                'placeholder' => __('admin.phone')
            ],
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $action = 'add';
        $route = route('employees.store');
        $method = 'post';
        $controls = $this->controls();
        return view('admin.employee.edit', compact('action', 'route', 'method', 'controls'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEmployeeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreEmployeeRequest $request)
    {
        // save entry
        $employee = Employee::create([
            'first_name' => [
                'en'     => $request['first_name_en'],
                'ua'     => $request['first_name_ua']
            ],
            'last_name'  => [
                'en'     => $request['last_name_en'],
                'ua'     => $request['last_name_ua']
            ],
            'email'      => $request['email'],
            'phone'      => $request['phone'],
            'company_id' => $request['company_id']
        ]);
        // send email to all managers
        Mail::to(User::all())->send(new NewEmployee($employee));
        // same with async worker
        //Mail::to(User::all())->queue(new NewEmployee($employee));
        // show result
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Employee'));
        $modal = new ModalInfo(
            __('admin.create_success_header', ['entity' => $genitive]),
            __('admin.create_success_text', ['entity' => $genitive, 'id' => $employee->id]),
        );
        return Redirect::route('employees.index')->with('modal', $modal);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Employee $employee)
    {
        $action = 'edit';
        $route = route('employees.update', ['employee' => $employee['id']]);
        $method = 'put';
        $controls = $this->controls();
        foreach ($controls as &$control)
        {
            $field = $control['name'];
            if (str_contains($field, 'name'))
            {
                preg_match('/(.+)_(.+)/', $field, $matches);
                $control['value'] = $employee->getTranslation($matches[1], $matches[2]);
            }
            else
            {
                $control['value'] = $employee[$field];
            }
        }
        return view('admin.employee.edit', compact('action', 'route', 'method', 'controls'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmployeeRequest  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->setTranslation('first_name', 'ua', $request['first_name_ua']);
        $employee->setTranslation('first_name', 'en', $request['first_name_en']);
        $employee->setTranslation('last_name', 'ua', $request['last_name_ua']);
        $employee->setTranslation('last_name', 'en', $request['last_name_en']);
        $employee->email = $request['email'];
        $employee->phone = $request['phone'];
        $employee->company_id = $request['company_id'];
        $employee->save();
        // show result
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Employee'));
        $modal = new ModalInfo(
            __('admin.edit_success_header', ['entity' => $genitive]),
            __('admin.edit_success_text', ['entity' => $genitive, 'id' => $employee->id]),
        );
        return Redirect::route('employees.edit', ['employee' => $employee->id])->with('modal', $modal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Employee $employee)
    {
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Employee'));
        $modal = new ModalInfo(
            __('admin.delete_success_header', ['entity' => $genitive]),
            __('admin.delete_success_text', ['entity' => $genitive, 'id' => $employee['id']]),
        );

        $employee->delete();

        return Redirect::route('employees.index')->with('modal', $modal);
    }
}
