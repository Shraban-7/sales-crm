<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contacts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contacts\StoreContactRequest;
use App\Http\Requests\Contacts\UpdateContactRequest;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contact::class);

        $search = trim((string) $request->query('search', ''));
        $contacts = Contact::query()
            ->with(['company', 'owner'])
            ->visibleTo($request->user())
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        $companies = Company::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name']);

        return view('pages.contacts.index', compact('contacts', 'companies', 'search'));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $this->authorize('create', Contact::class);

        $user = $request->user();
        $contact = Contact::query()->create(array_merge($request->validated(), [
            'team_id' => $user->team_id,
            'owner_id' => $user->id,
            'is_primary_decision_maker' => $request->boolean('is_primary_decision_maker'),
            'do_not_contact' => $request->boolean('do_not_contact'),
        ]));

        return redirect()->route('contacts.show', $contact)->with('status', 'Contact created.');
    }

    public function show(Request $request, Contact $contact): View
    {
        $this->authorize('view', $contact);

        $contact->load([
            'company',
            'owner',
            'deals.stage',
            'quotations',
            'activities.user',
            'tasks.assignee',
            'followUps.owner',
            'attachments.uploader',
        ]);

        $companies = Company::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name']);

        return view('pages.contacts.show', compact('contact', 'companies'));
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $contact->update(array_merge($request->validated(), [
            'is_primary_decision_maker' => $request->boolean('is_primary_decision_maker'),
            'do_not_contact' => $request->boolean('do_not_contact'),
        ]));

        return redirect()->route('contacts.show', $contact)->with('status', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return redirect()->route('contacts.index')->with('status', 'Contact deleted.');
    }
}
