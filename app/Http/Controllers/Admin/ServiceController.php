<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\ServiceStep;
use App\Models\ServiceSubmission;
use Illuminate\Support\Str;

class ServiceController extends Controller
{

    public function store(StoreServiceRequest $request)
    {
        DB::beginTransaction();

        try {

            $service = Service::create([
                'title' => $request->title,
                'icon' => $request->icon,
                'price' => $request->price,
                'short_service_detail' => $request->short_service_detail,
                'description' => $request->description,
            ]);

            foreach ($request->steps as $stepIndex => $step) {

                $stepModel = $service->steps()->create([
                    'title' => $step['title'],
                    'order' => $stepIndex,
                ]);

                foreach ($step['fields'] as $fieldIndex => $field) {

                    $documentKey = Str::slug($field['document_key'], '_');

                    $stepModel->fields()->create([
                        'label' => $field['label'],
                        'document_key' => $documentKey,
                        'type' => $field['type'],
                        'placeholder' => $field['placeholder'] ?? null,
                        'required' => $field['required'] ?? false,
                        'options' => $field['options'] ?? null,
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

}
