<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailTemplateController extends Controller
{
    public function emailConfig()
    {
        $data['pageTitle'] = 'Email Configuration';
        $data['navGeneralSettingsActiveClass'] = 'active';
        $data['subNavEmailConfigActiveClass'] = 'active';

        $data['email'] = GeneralSetting::select('id', 'email_method', 'email_config', 'site_email')->first();

        $data['masked_userName'] = $data['email']->email_config->smtp_username;
        $data['masked_password'] = $data['email']->email_config->smtp_password;

        return view('backend.email.config')->with($data);
    }

    public function emailConfigUpdate(Request $request)
    {
        $data = $request->validate([
            'site_email' => 'required|email',
            'email_method' => 'required',
            'email_config' => 'required_if:email_method,==,smtp',
            'email_config.*' => 'required_if:email_method,==,smtp',
        ]);

        $general = GeneralSetting::first();

        $general->update($data);

        Cache::forget('general_setting');

        return redirect()->back()->with('success', 'Email Setting Updated Successfully');

    }

    public function emailTemplates()
    {
        $data['pageTitle'] = 'Notification Templates';
        $data['navGeneralSettingsActiveClass'] = 'active';
        $data['subNavEmailTemplatesActiveClass'] = 'active';
        $data['emailTemplates'] = EmailTemplate::latest()->paginate();

        return view('backend.email.templates')->with($data);
    }

    public function emailTemplatesEdit(EmailTemplate $template)
    {
        $pageTitle = 'Template Edit';
        $navGeneralSettingsActiveClass = 'active';
        $subNavEmailTemplatesActiveClass = 'active';

        return view('backend.email.edit', compact('navGeneralSettingsActiveClass', 'subNavEmailTemplatesActiveClass', 'pageTitle', 'template'));
    }

    public function emailTemplatesUpdate(Request $request, EmailTemplate $template)
    {
        $data = $request->validate([
            'subject' => 'required',
            'template' => 'required',
        ]);

        $template->update($data);

        return redirect()->route('admin.email.templates')->with('success', 'Notify Template Updated Successfully');

    }
}
