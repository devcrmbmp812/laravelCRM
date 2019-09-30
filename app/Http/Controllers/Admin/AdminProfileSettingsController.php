<?php

namespace App\Http\Controllers\Admin;

class AdminProfileSettingsController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-user';
        $this->pageTitle = __('app.menu.profileSettings');
        $this->tutorialUrl = 'https://www.youtube.com/watch?v=GXtfJ3DVCmQ';
    }

    public function index(){
        $this->userDetail = $this->user;
        $this->employeeDetail = $this->user->employee_details;

        return view('admin.profile.index', $this->data);
    }

}
