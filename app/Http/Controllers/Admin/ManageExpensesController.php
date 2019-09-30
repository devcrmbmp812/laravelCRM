<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\EmployeeDetails;
use App\Expense;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Expenses\StoreExpense;
use App\Notifications\NewExpenseAdmin;
use App\Notifications\NewExpenseStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManageExpensesController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.expenses');
        $this->pageIcon = 'ti-shopping-cart';
        $this->middleware(function ($request, $next) {
            if(!in_array('expenses',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index(){
        $this->employees = User::allEmployees();
        return view('admin.expenses.index', $this->data);

    }

    public function create(){
        $this->currencies = Currency::all();

        $this->employees = EmployeeDetails::select('id', 'user_id')
            ->with(['user' => function($q) {
                $q->select('id', 'name');
            }])
            ->get();

        $employees = $this->employees->toArray();

        foreach ($this->employees as $employee) {
            $filtered_array = array_filter($employees, function ($item) use ($employee){
                return $item['user']['id'] == $employee->user->id;
            });

            $projects = [];

            foreach ($employee->user->member as $member) {
                if (!is_null($member->project)) {
                    array_push($projects, $member->project()->select('id', 'project_name')->first()->toArray());
                }
            }
            $employees[key($filtered_array)]['user'] = array_add(reset($filtered_array)['user'], 'projects', $projects);
        }

        $this->employees = $employees;
        return view('admin.expenses.create', $this->data);
    }

    public function store(StoreExpense $request){
        $expense = new Expense();
        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::createFromFormat($this->global->date_format, $request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price,2);
        $expense->currency_id = $request->currency_id;
        $expense->user_id = $request->user_id;
        
        if ($request->project_id > 0) {
            $expense->project_id = $request->project_id;
        }
        if ($request->hasFile('bill')) {
            $expense->bill = $request->bill->hashName();
            $request->bill->store('expense-invoice');
            $img = Image::make('user-uploads/expense-invoice/' . $expense->bill);
            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save();
        }

        $expense->status = 'approved';
        $expense->save();

        $user = User::findOrFail($expense->user_id);
        $user->notify(new NewExpenseAdmin($expense));

        return Reply::redirect(route('admin.expenses.index'), __('messages.expenseSuccess'));
    }

    public function data(Request $request) {

        $payments = Expense::select('expenses.id', 'expenses.item_name', 'expenses.user_id', 'expenses.price', 'users.name', 'expenses.purchase_date','expenses.currency_id','currencies.currency_symbol','expenses.status','expenses.purchase_from' )
        ->join('users', 'users.id', 'expenses.user_id')
        ->join('currencies', 'currencies.id', 'expenses.currency_id');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(expenses.`purchase_date`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(expenses.`purchase_date`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $payments = $payments->where('expenses.status', '=', $request->status);
        }
        if ($request->employee != 'all' && !is_null($request->employee)) {
            $payments = $payments->where('expenses.user_id', '=', $request->employee);
        }

        $payments = $payments->get();

        return DataTables::of($payments)
            ->addColumn('action', function ($row) {
                return '<a href="' . route("admin.expenses.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-expense-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
            })
            ->editColumn('price', function ($row) {
                if(!is_null($row->purchase_date)) {
                    return $row->total_amount;
                }
                return '-';
            })
            ->editColumn('user_id', function ($row) {
                return '<a href="'.route('admin.employees.show', $row->user_id).'">'.ucwords($row->name).'</a>';
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'pending'){
                    return '<label class="label label-warning">'.strtoupper($row->status).'</label>';
                }
                else if($row->status == 'approved'){
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }else{
                    return '<label class="label label-danger">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn(
                'purchase_date',
                function ($row) {
                    if(!is_null($row->purchase_date)){
                        return $row->purchase_date->timezone($this->global->timezone)->format($this->global->date_format);
                    }
                }
            )
            ->rawColumns(['action', 'status', 'user_id'])
            ->removeColumn('currency_id')
            ->removeColumn('name')
            ->removeColumn('currency_symbol')
            ->removeColumn('updated_at')
            ->removeColumn('created_at')
            ->make(true);
    }

    public function edit($id) {
        $this->currencies = Currency::all();
        $this->expense = Expense::findOrFail($id);

        $this->employees = EmployeeDetails::select('id', 'user_id')
            ->with(['user' => function($q) {
                $q->select('id', 'name');
            }])
            ->get();

        $employees = $this->employees->toArray();

        foreach ($this->employees as $employee) {
            $filtered_array = array_filter($employees, function ($item) use ($employee){
                return $item['user']['id'] == $employee->user->id;
            });

            $projects = [];

            foreach ($employee->user->member as $member) {
                if (!is_null($member->project)) {
                    array_push($projects, $member->project()->select('id', 'project_name')->first()->toArray());
                }
            }
            $employees[key($filtered_array)]['user'] = array_add(reset($filtered_array)['user'], 'projects', $projects);
        }

        $this->employees = $employees;
        return view('admin.expenses.edit', $this->data);
    }

    public function update(StoreExpense $request, $id){
        $expense = Expense::findOrFail($id);
        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::createFromFormat($this->global->date_format, $request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price,2);
        $expense->currency_id = $request->currency_id;
        $expense->user_id = $request->user_id;

        if ($request->project_id > 0) {
            $expense->project_id = $request->project_id;
        } else {
            $expense->project_id = null;
        }

        if ($request->hasFile('bill')) {
            Files::deleteFile($expense->bill,'expense-invoice');
            $expense->bill = $request->bill->hashName();
            $request->bill->store('expense-invoice');
            $img = Image::make('user-uploads/expense-invoice/' . $expense->bill);
            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save();
        }

        $expense->status = $request->status;
        $expense->save();

        //send welcome email notification
        $user = User::findOrFail($expense->user_id);
        $user->notify(new NewExpenseStatus($expense));

        return Reply::redirect(route('admin.expenses.index'), __('messages.expenseUpdateSuccess'));
    }

    public function destroy($id) {
        Expense::destroy($id);
        return Reply::success(__('messages.expenseDeleted'));
    }

    public function export($startDate, $endDate, $status, $employee) {
        $payments = Expense::select('expenses.id', 'expenses.item_name', 'expenses.price', 'users.name', 'expenses.purchase_date','expenses.currency_id','currencies.currency_symbol','expenses.purchase_from', 'expenses.status')
            ->join('users', 'users.id', 'expenses.user_id')
            ->join('currencies', 'currencies.id', 'expenses.currency_id');

        if($startDate !== null && $startDate != 'null' && $startDate != ''){
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(expenses.`purchase_date`)'), '>=', $startDate);
        }

        if($endDate !== null && $endDate != 'null' && $endDate != ''){
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(expenses.`purchase_date`)'), '<=', $endDate);
        }

        if($status != 'all' && !is_null($status)){
            $payments = $payments->where('expenses.status', '=', $status);
        }

        if($employee != 'all' && !is_null($employee)){
            $payments = $payments->where('expenses.user_id', '=', $employee);
        }


        $attributes =  ['price', 'currency_symbol', 'purchase_date', 'user_id', 'currency_id', 'currency'];

        $payments = $payments->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Item Name','Employee','Purchased From','Status', 'Price', 'Purchase Date'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('expense', function($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Expense');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('expense file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));

                });

            });

        })->download('xlsx');
    }

}
