<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Project;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storage = new DocumentStorageService(),
    ) {}

    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Customer::where('company_id', $company->id)->with('documents');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('contents.property.customers.index', compact('customers'));
    }

    public function create()
    {
        $projects = $this->companyProjects();

        return view('contents.property.customers.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $this->validateCustomer($request);
        $validated['company_id'] = $company->id;
        $validated['project_id'] = $this->resolveProjectId($request->input('project_id'), $company->id);

        $customer = Customer::create($validated);

        $this->syncDocuments($request, $customer, $company->id);
        $customer->refreshProfileState();

        // Optionally generate a secure self-service profile link straight away.
        if ($request->boolean('generate_profile_link')) {
            $customer->generateProfileLink();

            return redirect("/customers/{$customer->uuid}/edit")
                ->with('success', 'Customer created successfully. Profile link copied.')
                ->with('copy_link', $customer->profile_link);
        }

        return redirect('/customers')->with('success', 'Customer created successfully.');
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with('documents')
            ->firstOrFail();

        $projects = $this->companyProjects();

        return view('contents.property.customers.edit', compact('customer', 'projects'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->with('documents')->firstOrFail();

        $validated = $this->validateCustomer($request);
        $validated['project_id'] = $this->resolveProjectId($request->input('project_id'), $company->id);

        $customer->fill($validated);

        // Admin verification drives the verified timestamp used for locking.
        if ($customer->status === 'verified' && ! $customer->profile_verified_at) {
            $customer->profile_verified_at = now();
        } elseif ($customer->status !== 'verified') {
            $customer->profile_verified_at = null;
        }

        $customer->save();

        $this->syncDocuments($request, $customer, $company->id);
        $customer->refreshProfileState();

        return redirect('/customers')->with('success', 'Customer updated successfully.');
    }

    /**
     * Create a fresh profile-completion token (invalidating any previous one),
     * reset the 30-day expiry and copy the new link to the clipboard.
     */
    public function regenerateProfileLink(string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $customer->generateProfileLink();

        return redirect()->back()
            ->with('success', 'Profile link regenerated and copied.')
            ->with('copy_link', $customer->profile_link);
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->with('documents')->firstOrFail();

        foreach ($customer->documents as $document) {
            if ($this->storage->exists($document)) {
                $this->storage->delete($document);
            }
            $document->delete();
        }

        $customer->delete();

        return redirect('/customers')->with('success', 'Customer deleted successfully.');
    }

    public function destroyDocument(string $uuid, string $documentUuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $document = Document::forCompany($company->id)
            ->where('documentable_type', Customer::class)
            ->where('documentable_id', $customer->id)
            ->where('uuid', $documentUuid)
            ->firstOrFail();

        if ($this->storage->exists($document)) {
            $this->storage->delete($document);
        }

        $document->delete();
        $customer->load('documents');
        $customer->refreshProfileState();

        return redirect("/customers/{$customer->uuid}/edit")->with('success', 'Document removed.');
    }

    /**
     * Only Name and Mobile are required — everything else is optional and can
     * be completed later.
     */
    private function validateCustomer(Request $request): array
    {
        $docRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'];

        return $request->validate([
            // Required (quick create)
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],

            // Optional (quick create)
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'in:lead,customer,verified'],
            'type' => ['nullable', 'in:individual,business'],
            'company_name' => ['nullable', 'string', 'max:255'],

            // Additional › Personal
            'full_name_en' => ['nullable', 'string', 'max:255'],
            'full_name_bn' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_name_bn' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_name_bn' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'profession' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],

            // Additional › Contact
            'alternative_mobile' => ['nullable', 'string', 'max:20'],
            'present_address' => ['nullable', 'string', 'max:1000'],
            'permanent_address' => ['nullable', 'string', 'max:1000'],

            // Additional › Identity
            'nid_number' => ['nullable', 'string', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'driving_license_number' => ['nullable', 'string', 'max:255'],

            // Additional › Nominee
            'nominee_name' => ['nullable', 'string', 'max:255'],
            'nominee_relationship' => ['nullable', 'string', 'max:255'],
            'nominee_mobile' => ['nullable', 'string', 'max:20'],
            'nominee_address' => ['nullable', 'string', 'max:1000'],
            'nominee_nid_number' => ['nullable', 'string', 'max:255'],

            // Additional › Documents (image/PDF only, max 3 MB each)
            'documents.photo' => $docRules,
            'documents.nid_front' => $docRules,
            'documents.nid_back' => $docRules,
            'documents.tin' => $docRules,
            'documents.passport' => $docRules,
            'documents.nominee_nid' => $docRules,
        ]);
    }

    /**
     * Upload any provided document files, replacing an existing file of the
     * same type. Uses the existing polymorphic document system.
     */
    private function syncDocuments(Request $request, Customer $customer, int $companyId): void
    {
        foreach (array_keys(Customer::DOCUMENT_TYPES) as $type) {
            if (! $request->hasFile("documents.{$type}")) {
                continue;
            }

            $customer->load('documents');
            if ($existing = $customer->documentOfType($type)) {
                if ($this->storage->exists($existing)) {
                    $this->storage->delete($existing);
                }
                $existing->delete();
            }

            $meta = $this->storage->upload(
                $request->file("documents.{$type}"),
                "companies/{$companyId}/customers/{$customer->id}",
            );

            Document::create([
                'company_id' => $companyId,
                'documentable_type' => Customer::class,
                'documentable_id' => $customer->id,
                'title' => Customer::DOCUMENT_TYPES[$type],
                'file_name' => $meta['file_name'],
                'file_path' => $meta['path'],
                'file_size' => $meta['size'],
                'mime_type' => $meta['mime_type'],
                'disk' => $meta['disk'],
                'uploaded_by' => Auth::id(),
                'metadata' => ['customer_document_type' => $type],
            ]);
        }
    }

    private function resolveProjectId(?string $uuid, int $companyId): ?int
    {
        if (empty($uuid)) {
            return null;
        }

        return Project::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->value('id');
    }

    private function companyProjects()
    {
        return Project::where('company_id', app('currentCompany')->id)
            ->orderBy('name')
            ->get(['uuid', 'name']);
    }
}
