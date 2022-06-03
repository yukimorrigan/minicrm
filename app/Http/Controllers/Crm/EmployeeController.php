<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
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
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'set_language']);
    }

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
     * @param Request $request
     */
    public function page(Request $request)
    {
        /** @var string $draw */
        /** @var string $start */
        /** @var string $rowsPerPage */
        /** @var string $columnIndex */
        /** @var string $columnName */
        /** @var string $columnSortOrder */
        /** @var string $searchValue */
        extract($this->getPageParams($request));

        $lang = config('app.locale');
        if ($columnName === 'first_name' || $columnName === 'last_name' )
            $columnName .= "_$lang";

        // Fetch records
        $records = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->orderBy($columnName, $columnSortOrder)
            ->where('employees.first_name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.first_name_ua', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.last_name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.last_name_ua', 'like', '%' .$searchValue . '%')
            ->orWhere('companies.name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('companies.name_ua', 'like', '%' .$searchValue . '%')
            ->select('employees.*', DB::raw("companies.name_$lang as company_name"))
            ->skip($start)
            ->take($rowsPerPage)
            ->get();

        $data = [];
        foreach($records as $record)
        {
            $editButton = new AdminButton('employees', 'employee', 'edit', $record['id']);
            $deleteButton = new AdminButton('employees', 'employee', 'destroy', $record['id'], true);
            $data[] = [
                "id"            => $record['id'],
                "company_name"  => $record["company_name"],
                "first_name"    => $record["first_name_$lang"],
                "last_name"     => $record["last_name_$lang"],
                "email"         => $record['email'],
                "phone"         => $record['phone'],
                "edit"          => $editButton->render()->render(),
                "delete"        => $deleteButton->render()->render()
            ];
        }

        // Total records
        $totalRecords = Employee::select('count(*) as allcount')->count();
        $totalFilteredRecords = Employee::select("count(*) as allcount")
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.first_name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.first_name_ua', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.last_name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('employees.last_name_ua', 'like', '%' .$searchValue . '%')
            ->orWhere('companies.name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('companies.name_ua', 'like', '%' .$searchValue . '%')
            ->count();

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRecords,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $lang = config('app.locale');
        $companies = Company::all()->pluck( "name_$lang", 'id');
        $controls = [
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
                'values' => $companies,
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
        return view('admin.employee.create', ['controls' => $controls]);
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
            'first_name_en' => $request['first_name_en'],
            'first_name_ua' => $request['first_name_ua'],
            'last_name_en' => $request['last_name_en'],
            'last_name_ua' => $request['last_name_ua'],
            'email'   => $request['email'],
            'phone'   => $request['phone'],
            'company_id'    => $request['company_id']
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
        $lang = config('app.locale');
        $companies = Company::all()->pluck( "name_$lang", 'id');
        $controls = [
            [
                'type' => 'text',
                'name' => 'first_name_ua',
                'placeholder' => __('admin.employee_name_ua'),
                'value' => $employee['first_name_ua']
            ],
            [
                'type' => 'text',
                'name' => 'last_name_ua',
                'placeholder' => __('admin.employee_lastname_ua'),
                'value' => $employee['last_name_ua']
            ],
            [
                'type' => 'text',
                'name' => 'first_name_en',
                'placeholder' => __('admin.employee_name_en'),
                'value' => $employee['first_name_en']
            ],
            [
                'type' => 'text',
                'name' => 'last_name_en',
                'placeholder' => __('admin.employee_lastname_en'),
                'value' => $employee['last_name_en']
            ],
            [
                'type' => 'select',
                'name' => 'company_id',
                'placeholder' => __('admin.company_name'),
                'values' => $companies,
                'value' => $employee['company_id']
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'placeholder' => __('admin.email'),
                'value' => $employee['email']
            ],
            [
                'type' => 'text',
                'name' => 'phone',
                'placeholder' => __('admin.phone'),
                'value' => $employee['phone']
            ],
        ];
        return view('admin.employee.edit', ['controls' => $controls, 'id' => $employee['id']]);
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
        $employee->first_name_ua = $request['first_name_ua'];
        $employee->first_name_en = $request['first_name_en'];
        $employee->last_name_ua = $request['last_name_ua'];
        $employee->last_name_en = $request['last_name_en'];
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
