<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Document;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated customer self-service profile completion.
 *
 * Reached via a secure random token (/customer-profile/{token}). No login and
 * no company context — the company is resolved from the customer record. The
 * page is read-only once the profile has been submitted, and shows only the
 * name/mobile once an admin has verified (locked) the profile.
 */
class PublicCustomerProfileController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storage = new DocumentStorageService(),
    ) {}

    public function show(string $token)
    {
        $customer = $this->findByToken($token);

        if (! $customer) {
            return response()->view('public.customer-profile', ['state' => 'invalid'], 404);
        }

        return view('public.customer-profile', [
            'state' => $this->resolveState($customer),
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, string $token)
    {
        $customer = $this->findByToken($token);

        if (! $customer) {
            return response()->view('public.customer-profile', ['state' => 'invalid'], 404);
        }

        $state = $this->resolveState($customer);

        // Only an editable profile may be submitted.
        if ($state !== 'editable') {
            return redirect("/customer-profile/{$token}");
        }

        $docRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'];

        // Photo / NID front / NID back are mandatory, but only force a new
        // upload when the customer has not already provided that document.
        $requiredDoc = fn (string $type) => array_merge(
            [$customer->documentOfType($type) ? 'nullable' : 'required'],
            ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'],
        );

        $hasJoint = $request->boolean('has_joint_owner');

        $validated = $request->validate([
            // Personal (required)
            'full_name_en' => ['required', 'string', 'max:255'],
            'full_name_bn' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'father_name_bn' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_name_bn' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            // Personal (optional)
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'religion' => ['nullable', 'string', 'max:255'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            // Contact
            'phone' => ['required', 'string', 'max:30'],
            'present_address' => ['required', 'string', 'max:1000'],
            'permanent_address' => ['required', 'string', 'max:1000'],
            'alternative_mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            // Identity
            'nid_number' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'driving_license_number' => ['nullable', 'string', 'max:255'],
            // Nominee
            'nominee_name' => ['required', 'string', 'max:255'],
            'nominee_relationship' => ['required', 'string', 'max:255'],
            'nominee_mobile' => ['required', 'string', 'max:20'],
            'nominee_address' => ['nullable', 'string', 'max:1000'],
            'nominee_nid_number' => ['nullable', 'string', 'max:255'],
            // Financial (optional)
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            // Emergency contact (optional)
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_mobile' => ['nullable', 'string', 'max:20'],
            // Joint owner
            'has_joint_owner' => ['nullable', 'boolean'],
            'joint_owner_name' => [$hasJoint ? 'required' : 'nullable', 'string', 'max:255'],
            'joint_owner_mobile' => [$hasJoint ? 'required' : 'nullable', 'string', 'max:20'],
            'joint_owner_nid' => [$hasJoint ? 'required' : 'nullable', 'string', 'max:255'],
            'joint_owner_address' => ['nullable', 'string', 'max:1000'],
            // Documents (required)
            'documents.photo' => $requiredDoc('photo'),
            'documents.nid_front' => $requiredDoc('nid_front'),
            'documents.nid_back' => $requiredDoc('nid_back'),
            // Documents (optional)
            'documents.tin' => $docRules,
            'documents.passport' => $docRules,
            'documents.nominee_nid' => $docRules,
            'documents.joint_owner_photo' => $docRules,
        ]);

        // Overwrite existing profile information with the submitted values.
        $customer->fill(collect($validated)->only(Customer::PUBLIC_FILLABLE)->all());
        $customer->has_joint_owner = $hasJoint;
        $customer->profile_completed_at = now();
        $customer->save();

        $this->uploadDocuments($request, $customer);
        $customer->load('documents');
        $customer->refreshProfileState();

        return redirect("/customer-profile/{$token}")
            ->with('success', 'Your profile has been submitted successfully. Thank you.');
    }

    private function findByToken(string $token): ?Customer
    {
        if ($token === '') {
            return null;
        }

        return Customer::where('profile_token', $token)->with('documents')->first();
    }

    /**
     * Determine what the public visitor is allowed to see/do. Order matters:
     * locked/completed take precedence over expiry so a customer who submitted
     * in time never sees an "expired" message.
     */
    private function resolveState(Customer $customer): string
    {
        if ($customer->profile_locked) {
            return 'locked';
        }

        if ($customer->isProfileCompleted()) {
            return 'completed';
        }

        if ($customer->isProfileLinkExpired()) {
            return 'expired';
        }

        return 'editable';
    }

    private function uploadDocuments(Request $request, Customer $customer): void
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
                "companies/{$customer->company_id}/customers/{$customer->id}",
            );

            Document::create([
                'company_id' => $customer->company_id,
                'documentable_type' => Customer::class,
                'documentable_id' => $customer->id,
                'title' => Customer::DOCUMENT_TYPES[$type],
                'file_name' => $meta['file_name'],
                'file_path' => $meta['path'],
                'file_size' => $meta['size'],
                'mime_type' => $meta['mime_type'],
                'disk' => $meta['disk'],
                'uploaded_by' => null,
                'metadata' => ['customer_document_type' => $type],
            ]);
        }
    }
}
