<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Service;
use Illuminate\Support\Str;
use App\Models\ServiceStep;
use Illuminate\Http\Request;
use App\Models\ServiceSubmission;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\ServiceField;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $services = Service::with([
            'steps' => function ($q) {
                $q->orderBy('order')
                ->select('id', 'service_id', 'title', 'order');
            },
            'steps.fields' => function ($q) {
                $q->orderBy('order')
                ->select('id', 'service_step_id', 'label', 'document_key', 'type', 'placeholder', 'required', 'options', 'column', 'order');
            }
        ])
        ->select('id','title','icon','price','short_service_detail','description','effective_date','expiry_date','is_active','updated_at')
        ->orderBy('id', 'desc')
        ->paginate($perPage);

        $services->getCollection()->transform(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'icon' => $service->icon_url,
                'price' => $service->price,
                'short_service_detail' => $service->short_service_detail,
                'description' => $service->description,
                'effective_date' => $service->effective_date,
                'expiry_date' => $service->expiry_date,
                'is_active' => $service->is_active,
                'updated_at' => $service->updated_at ? $service->updated_at->format('Y-m-d') : null,
                'steps' => $service->steps->map(function ($step) {
                    return [
                        'id' => $step->id,
                        'title' => $step->title,
                        'order' => $step->order,
                        'fields' => $step->fields->map(function ($field) {
                            return [
                                'id' => $field->id,
                                'label' => $field->label,
                                'document_key' => $field->document_key,
                                'type' => $field->type,
                                'placeholder' => $field->placeholder,
                                'required' => $field->required,
                                'options' => $field->options,
                                'column' => $field->column,
                                'order' => $field->order,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Service forms retrieved successfully',
            'data' => $services
        ]);
    }

    public function store(StoreServiceRequest $request)
    {
        DB::beginTransaction();

        try {

            $allKeys = collect($request->steps)
                ->flatMap(fn($step) => $step['fields'])
                ->pluck('document_key')
                ->map(fn($key) => Str::slug($key, '_'));

            if ($allKeys->count() !== $allKeys->unique()->count()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate document_key found in this service'
                ], 422);
            }

            $effectiveCount = collect($request->steps)
                ->flatMap(fn($step) => $step['fields'])
                ->where('type', 'effective_date')
                ->count();

            if ($effectiveCount > 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only one effective_date field allowed'
                ], 422);
            }

            $iconPath = null;
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('services/icons', 'public');
            }

            $service = Service::create([
                'title' => $request->title,
                'icon' => $iconPath,
                'price' => $request->price,
                'short_service_detail' => $request->short_service_detail,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            foreach ($request->steps as $stepIndex => $step) {

                $stepModel = $service->steps()->create([
                    'title' => $step['title'],
                    'order' => $stepIndex,
                ]);

                foreach ($step['fields'] as $fieldIndex => $field) {

                    $documentKey = Str::slug($field['document_key'], '_');

                    if (in_array($field['type'], ['select', 'radio']) && empty($field['options'])) {
                        throw new Exception("Options required for {$field['type']} field: {$documentKey}");
                    }

                    $stepModel->fields()->create([
                        'service_id' => $service->id,
                        'label' => $field['label'],
                        'document_key' => $documentKey,
                        'type' => $field['type'],
                        'placeholder' => $field['placeholder'] ?? null,
                        'required' => $field['required'] ?? false,
                        'options' => $field['options'] ?? null,
                        'column' => $field['column'] ?? 1,
                        'order' => $fieldIndex,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Service form created successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Service $service)
    {
        $service = Service::with([
            'steps' => function ($q) {
                $q->orderBy('order')
                ->select('id', 'service_id', 'title', 'order');
            },
            'steps.fields' => function ($q) {
                $q->orderBy('order')
                ->select('id', 'service_id', 'service_step_id', 'label', 'document_key', 'type',
                    'placeholder', 'required', 'options', 'column', 'order'
                );
            }
        ])
        ->select('id','title', 'icon', 'price', 'short_service_detail','description','effective_date','expiry_date','is_active')
        ->findOrFail($service->id);

        $serviceData = $service->toArray();

        $serviceData['icon'] = $service->icon ? asset('storage/' . $service->icon) : null;

        return response()->json([
            'status' => true,
            'message' => 'Service form retrieved successfully',
            'data' => $serviceData
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        DB::beginTransaction();

        try {
            $allKeys = collect($request->steps)
                ->flatMap(fn($step) => $step['fields'])
                ->pluck('document_key')
                ->map(fn($key) => Str::slug($key, '_'));

            if ($allKeys->count() !== $allKeys->unique()->count()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate document_key found in this service'
                ], 422);
            }

            $effectiveCount = collect($request->steps)
                ->flatMap(fn($step) => $step['fields'])
                ->where('type', 'effective_date')
                ->count();

            if ($effectiveCount > 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only one effective_date field allowed'
                ], 422);
            }

            if ($request->hasFile('icon')) {
                if ($service->icon && Storage::disk('public')->exists($service->icon)) {
                    Storage::disk('public')->delete($service->icon);
                }

                $service->icon = $request->file('icon')->store('services/icons', 'public');
            }

            $service->update([
                'title' => $request->title,
                'price' => $request->price,
                'short_service_detail' => $request->short_service_detail,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $service->is_active,
            ]);

            $existingStepIds = $service->steps()->pluck('id')->toArray();

            foreach ($request->steps as $stepIndex => $stepData) {

                if (isset($stepData['id'])) {
                    $step = ServiceStep::findOrFail($stepData['id']);
                    $step->update([
                        'title' => $stepData['title'],
                        'order' => $stepIndex,
                    ]);
                } else {
                    $step = $service->steps()->create([
                        'title' => $stepData['title'],
                        'order' => $stepIndex,
                    ]);
                }

                $existingFieldIds = $step->fields()->pluck('id')->toArray();
                $submittedFieldIds = [];

                foreach ($stepData['fields'] as $fieldIndex => $fieldData) {

                    $documentKey = Str::slug($fieldData['document_key'], '_');

                    if (in_array($fieldData['type'], ['select', 'radio']) && empty($fieldData['options'])) {
                        throw new Exception("Options required for {$fieldData['type']} field: {$documentKey}");
                    }

                    if (isset($fieldData['id'])) {
                        $field = ServiceField::findOrFail($fieldData['id']);
                        $field->update([
                            'service_id' => $service->id,
                            'label' => $fieldData['label'],
                            'document_key' => $documentKey,
                            'type' => $fieldData['type'],
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'required' => $fieldData['required'] ?? false,
                            'options' => $fieldData['options'] ?? null,
                            'column' => $fieldData['column'] ?? 1,
                            'order' => $fieldIndex,
                        ]);
                        $submittedFieldIds[] = $field->id;

                    } else {
                        $newField = $step->fields()->create([
                            'service_id' => $service->id,
                            'label' => $fieldData['label'],
                            'document_key' => $documentKey,
                            'type' => $fieldData['type'],
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'required' => $fieldData['required'] ?? false,
                            'options' => $fieldData['options'] ?? null,
                            'column' => $fieldData['column'] ?? 1,
                            'order' => $fieldIndex,
                        ]);
                        $submittedFieldIds[] = $newField->id;
                    }
                }

                $fieldsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
                if (!empty($fieldsToDelete)) {
                    ServiceField::destroy($fieldsToDelete);
                }

                if (isset($stepData['id'])) {
                    $existingStepIds = array_diff($existingStepIds, [$stepData['id']]);
                }
            }

            if (!empty($existingStepIds)) {
                ServiceStep::destroy($existingStepIds);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Service form updated successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();

            return response()->json([
                'status' => true,
                'message' => 'Service form deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function active(Request $request, Service $service)
    {
        $request->validate([
            'is_active' => 'required|in:1,0',
        ]);

        $service->is_active = $request->is_active;
        $service->save();

        return response()->json([
            'status' => true,
            'message' => 'Service form status updated successfully'
        ]);
    }

    public function documentKeys($serviceId)
    {
        $service = Service::with('steps.fields:id,service_step_id,document_key')
            ->findOrFail($serviceId);

        $keys = collect($service->steps)
            ->flatMap(fn($step) => $step->fields)
            ->pluck('document_key')
            ->unique()
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Document keys retrieved successfully',
            'data' => $keys
        ]);
    }

    public function value(Request $request, $serviceId)
    {
        $request->validate([
            'document_key' => 'required|string',
            'submission_id' => 'required|integer'
        ]);

        $documentKey = $request->document_key;
        $submissionId = $request->submission_id;

        $submission = ServiceSubmission::where('id', $submissionId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$submission) {
            return response()->json([
                'status' => false,
                'message' => 'No submission found'
            ], 404);
        }

        $data = $submission->data;

        return response()->json([
            'status' => true,
            'message' => 'Value retrieved successfully',
            'data' => [
                'document_key' => $documentKey,
                'value' => $data[$documentKey] ?? null
            ]
        ]);
    }

    public function documentUpload(Request $request, $serviceId)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'note' => 'required|string'
        ]);

        $service = Service::findOrFail($serviceId);

        if ($service->document && Storage::disk('public')->exists($service->document)) {
            Storage::disk('public')->delete($service->document);
        }

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();

        $path = $file->storeAs('main_document', $fileName, 'public');

        $service->document = $path;
        $service->note = $request->note;
        $service->save();

        return response()->json([
            'status' => true,
            'message' => 'Document uploaded successfully',
            'path' => $path,
        ]);
    }

}
