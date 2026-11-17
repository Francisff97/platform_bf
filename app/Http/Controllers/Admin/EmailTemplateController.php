<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $items = EmailTemplate::orderBy('key')->get();
        return view('admin.emailtpl.index', compact('items'));
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.emailtpl.edit', compact('template'));
    }

    public function update(Request $r, EmailTemplate $template)
    {
        $data = $r->validate([
            'subject'   => 'required|string|max:255',
            'body_html' => 'required|string',
            'enabled'   => 'sometimes|boolean',
        ]);
        $data['enabled'] = (bool) $r->boolean('enabled');

        $template->fill($data);
        $template->updated_by = optional($r->user())->id;
        $template->save();

        return back()->with('success', 'Template updated.');
    }
}