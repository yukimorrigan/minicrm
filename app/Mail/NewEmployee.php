<?php

namespace App\Mail;

use App\Facades\LocalizationService;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewEmployee extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The employee instance.
     *
     * @var \App\Models\Employee
     */
    public $model;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Employee $employee
     * @return void
     */
    public function __construct(Employee $employee)
    {
        $this->subject = __('emails.new_employee_subject');
        $this->model = $employee;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $lang = config('app.locale');
        $name = [$this->model["first_name_$lang"], $this->model["last_name_$lang"]];
        if ($lang === LocalizationService::default())
            $name = array_reverse($name);
        $name = implode(' ', $name);
        $employee = [
            'id' => $this->model['id'],
            __('Name') => $name,
            __('Email') => $this->model['email'],
            __('Phone') => $this->model['phone'],
            __('Company') => $this->model->company["name_$lang"],
        ];
        return $this->markdown('mails.new-employee', ['employee' => $employee]);
    }
}
