<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Traits\TableHelper;
use App\Models\Company;
use App\Http\Requests\Crm\Company\StoreCompanyRequest;
use App\Http\Requests\Crm\Company\UpdateCompanyRequest;
use App\View\Components\AdminButton;
use App\View\Components\ModalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
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
        return view('admin.company.index');
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
        if ($columnName === 'name')
            $columnName .= "_$lang";

        // Fetch records
        $records = Company::orderBy($columnName, $columnSortOrder)
            ->where('name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('name_ua', 'like', '%' .$searchValue . '%')
            ->skip($start)
            ->take($rowsPerPage)
            ->get();

        $data = [];
        foreach($records as $record)
        {
            $editButton = new AdminButton('companies', 'company', 'edit', $record['id']);
            $deleteButton = new AdminButton('companies', 'company', 'destroy', $record['id'], true);
            $data[] = [
                "id"            => $record['id'],
                "company_name"  => $record["name_$lang"],
                "email"         => $record['email'],
                "phone"         => $record['phone'],
                "website"       => '<a href="'.$record['website'].'" target="_blank">'.$record['website'].'</a>',
                "edit"          => $editButton->render()->render(),
                "delete"        => $deleteButton->render()->render()
            ];
        }

        // Total records
        $totalRecords = Company::select('count(*) as allcount')->count();
        $totalFilteredRecords = Company::select('count(*) as allcount')
            ->where('name_en', 'like', '%' .$searchValue . '%')
            ->orWhere('name_ua', 'like', '%' .$searchValue . '%')
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
        $controls = [
            [
                'type' => 'text',
                'name' => 'name_ua',
                'placeholder' => __('admin.company_name_ua')
            ],
            [
                'type' => 'text',
                'name' => 'name_en',
                'placeholder' => __('admin.company_name_en')
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
            [
                'type' => 'text',
                'name' => 'website',
                'placeholder' => __('admin.website')
            ],
            [
                'type' => 'image',
                'name' => 'logo',
                'placeholder' => __('admin.company_logo'),
                'value' => '/img/logo.png'
            ]
        ];
        return view('admin.company.create', ['controls' => $controls]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Crm\Company\StoreCompanyRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCompanyRequest $request)
    {
        // save entry
        $company = Company::create([
            'name_en' => $request['name_en'],
            'name_ua' => $request['name_ua'],
            'email'   => $request['email'],
            'phone'   => $request['phone'],
            'website'   => $request['website']
        ]);

        if (isset($request['logo']))
        {
            $ext = $request['logo']->getClientOriginalExtension();
            $company->logo = Storage::disk('public')->putFileAs(
                'companies',
                $request['logo'],
                "logo$company->id.$ext"
            );
            $company->save();
        }
        // show result
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Company'));
        $modal = new ModalInfo(
            __('admin.create_success_header', ['entity' => $genitive]),
            __('admin.create_success_text', ['entity' => $genitive, 'id' => $company->id]),
        );
        return Redirect::route('companies.index')->with('modal', $modal);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Company $company)
    {
        $controls = [
            [
                'type' => 'text',
                'name' => 'name_ua',
                'placeholder' => __('admin.company_name_ua'),
                'value' => $company['name_ua']
            ],
            [
                'type' => 'text',
                'name' => 'name_en',
                'placeholder' => __('admin.company_name_en'),
                'value' => $company['name_en']
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'placeholder' => __('admin.email'),
                'value' => $company['email']
            ],
            [
                'type' => 'text',
                'name' => 'phone',
                'placeholder' => __('admin.phone'),
                'value' => $company['phone']
            ],
            [
                'type' => 'text',
                'name' => 'website',
                'placeholder' => __('admin.website'),
                'value' => $company['website']
            ],
            [
                'type' => 'image',
                'name' => 'logo',
                'placeholder' => __('admin.company_logo'),
                'value' => empty($company['logo']) ? '/img/logo.png' : '/storage/'.$company['logo']
            ]
        ];
        return view('admin.company.edit', ['controls' => $controls, 'id' => $company['id']]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Crm\Company\UpdateCompanyRequest  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->name_ua = $request['name_ua'];
        $company->name_en = $request['name_en'];
        $company->email = $request['email'];
        $company->phone = $request['phone'];
        $company->website = $request['website'];
        if (isset($request['logo']))
        {
            // delete the file in case of a different extension
            Storage::disk('public')->delete($company->logo);
            // save new file
            $ext = $request['logo']->getClientOriginalExtension();
            $company->logo = Storage::disk('public')->putFileAs('companies', $request['logo'], "logo$company->id.$ext");
        }
        $company->save();
        // show result
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Company'));
        $modal = new ModalInfo(
            __('admin.edit_success_header', ['entity' => $genitive]),
            __('admin.edit_success_text', ['entity' => $genitive, 'id' => $company->id]),
        );
        return Redirect::route('companies.edit', ['company' => $company->id])->with('modal', $modal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Company $company)
    {
        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Company'));
        $modal = new ModalInfo(
            __('admin.delete_success_header', ['entity' => $genitive]),
            __('admin.delete_success_text', ['entity' => $genitive, 'id' => $company['id']]),
        );

        $company->delete();

        return Redirect::route('companies.index')->with('modal', $modal);
    }
}
