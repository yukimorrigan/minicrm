<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Http\Traits\TableHelper;
use App\Models\Company;
use App\Http\Requests\Crm\Company\StoreCompanyRequest;
use App\Http\Requests\Crm\Company\UpdateCompanyRequest;
use App\View\Components\AdminButton;
use App\View\Components\ModalInfo;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    use TableHelper;

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
        if ($columnName === 'name')
            $columnName = "cast($columnName->>'$.$lang' as char)";

        // Fetch records
        $records = Company::orderByRaw("$columnName $columnSortOrder")
            ->when($searchValue, fn ($q) => $q
                ->where('name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('name->ua', 'like', '%' .$searchValue . '%')
            )
            ->paginate($request->length, ['*'], 'page', $page);

        $data = [];
        foreach($records as $record)
        {
            $editButton = new AdminButton('companies', 'company', 'edit', $record['id']);
            $deleteButton = new AdminButton('companies', 'company', 'destroy', $record['id'], true);
            $data[] = [
                "id"            => $record['id'],
                "name"          => $record['name'],
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
            ->when($searchValue, fn ($q) => $q
                ->where('name->en', 'like', '%' .$searchValue . '%')
                ->orWhere('name->ua', 'like', '%' .$searchValue . '%')
            )
            ->count();

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRecords,
            "data" => $data
        ]);
    }

    /**
     * Get form controls.
     * @return array[]
     */
    public function controls()
    {
        return [
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
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $action = 'add';
        $route = route('companies.store');
        $method = 'post';
        $controls = $this->controls();
        return view('admin.company.edit', compact('action', 'route', 'method', 'controls'));
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
        $path = '';
        if (isset($request['logo']))
            $path = $request->file('logo')->store('companies');

        $company = Company::create([
            'name'      => [
                'en'    => $request['name_en'],
                'ua'    => $request['name_ua'],
            ],
            'email'     => $request['email'],
            'phone'     => $request['phone'],
            'website'   => $request['website'],
            'logo'      => $path
        ]);
        $company->save();
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
     * @param Company $company
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function edit(Company $company)
    {
        if (auth()->user()->id !== $company->created_by()->first()->id)
            abort(403);

        $action = 'edit';
        $route = route('companies.update', ['company' => $company['id']]);
        $method = 'put';
        $controls = $this->controls();
        foreach ($controls as &$control)
        {
            $field = $control['name'];
            if ($field === 'logo')
            {
                empty($company['logo']) ?: $control['value'] = '/storage/'.$company['logo'];
            }
            else if (str_contains($field, 'name'))
            {
                $lang = explode('_', $field)[1];
                $control['value'] = $company->getTranslation('name', $lang);
            }
            else
            {
                $control['value'] = $company[$field];
            }
        }
        return view('admin.company.edit', compact('action', 'route', 'method', 'controls'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCompanyRequest $request
     * @param Company $company
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        if (auth()->user()->id !== $company->created_by()->first()->id)
            abort(403);

        $company->setTranslation('name', 'ua', $request['name_ua']);
        $company->setTranslation('name', 'en', $request['name_en']);
        $company->email = $request['email'];
        $company->phone = $request['phone'];
        $company->website = $request['website'];
        if (isset($request['logo']))
        {
            // save old file path
            $oldLogo = $company->logo;
            // save new file
            $company->logo = $request->file('logo')->store('companies');
            // delete the old file
            if (!empty($oldLogo))
                Storage::delete($oldLogo);
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
     * @param Company $company
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Company $company)
    {
        if (auth()->user()->id !== $company->created_by()->first()->id)
            abort(403);

        $genitive = $this->mb_ucfirst($this->getGenitiveCaseName('App\Models\Company'));
        $modal = new ModalInfo(
            __('admin.delete_success_header', ['entity' => $genitive]),
            __('admin.delete_success_text', ['entity' => $genitive, 'id' => $company['id']]),
        );

        if (!empty($company->logo))
            Storage::delete($company->logo);
        $company->delete();

        return Redirect::route('companies.index')->with('modal', $modal);
    }
}
