@php
    $lockSource = $lockSource ?? false;
    $currentSource = old('source', $sourceValue ?? 'company');
    $currentProject = old('project_id', $projectValue ?? '');
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select class="form-select" id="category_id" name="category_id" required>
            <option value="">— Select —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) old('category_id', $expense->category_id ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="amount" class="form-label">Amount (৳) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount"
            value="{{ old('amount', isset($expense) ? $expense->amount : '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="title" class="form-label">Title / Description</label>
        <input type="text" class="form-control" id="title" name="title"
            value="{{ old('title', $expense->title ?? '') }}" placeholder="e.g. Office electricity bill">
    </div>

    <div class="col-md-6">
        <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="expense_date" name="expense_date"
            value="{{ old('expense_date', isset($expense) && $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
    </div>

    @unless ($lockSource)
        <div class="col-md-6">
            <label for="source" class="form-label">Linked To</label>
            <select class="form-select" id="source" name="source" onchange="toggleProjectField(this.value)">
                <option value="company" {{ $currentSource === 'company' ? 'selected' : '' }}>Company (General)</option>
                <option value="project" {{ $currentSource === 'project' ? 'selected' : '' }}>Project</option>
            </select>
        </div>

        <div class="col-md-6" id="project-field" style="{{ $currentSource === 'project' ? '' : 'display:none;' }}">
            <label for="project_id" class="form-label">Project</label>
            <select class="form-select" id="project_id" name="project_id">
                <option value="">— Select —</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->uuid }}" {{ (string) $currentProject === (string) $project->uuid ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    @endunless

    <div class="col-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $expense->notes ?? '') }}</textarea>
    </div>
</div>

@unless ($lockSource)
    @push('scripts')
        <script>
            function toggleProjectField(value) {
                document.getElementById('project-field').style.display = value === 'project' ? '' : 'none';
            }
        </script>
    @endpush
@endunless
