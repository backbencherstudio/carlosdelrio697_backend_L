<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\ServiceSubmission;
use Carbon\Carbon;

class ServiceSubmitController extends Controller
{

    public function submit(Request $request, $serviceId)
    {
        DB::beginTransaction();

        try {
            $service = Service::with('steps.fields')->findOrFail($serviceId);

            $rules = $this->buildValidationRules($service);

            $validated = $request->validate($rules);

            $effectiveDateKey = null;

            foreach ($service->steps as $step) {
                foreach ($step->fields as $field) {
                    if ($field->type === 'effective_date') {
                        $effectiveDateKey = $field->document_key;
                    }
                }
            }

            if ($effectiveDateKey && isset($validated[$effectiveDateKey])) {

                $effectiveDate = Carbon::parse($validated[$effectiveDateKey]);

                $service->update([
                    'effective_date' => $effectiveDate,
                    'expiry_date' => $effectiveDate->copy()->addYear()
                ]);
            }

            $submission = ServiceSubmission::create([
                'service_id' => $service->id,
                'data' => $validated
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Form submitted successfully',
                'submission_id' => $submission->id
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function buildValidationRules($service)
    {
        $rules = [];

        foreach ($service->steps as $step) {
            foreach ($step->fields as $field) {

                $rule = [];

                if ($field->required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }

                switch ($field->type) {

                    case 'email':
                        $rule[] = 'email';
                        break;

                    case 'number':
                        $rule[] = 'numeric';
                        break;

                    case 'date':
                    case 'effective_date':
                        $rule[] = 'date';
                        break;

                    case 'radio':
                    case 'select':
                        $rule[] = 'string';

                        if (!empty($field->options)) {
                            $rule[] = 'in:' . implode(',', $field->options);
                        }
                        break;

                    case 'rich_text':
                    case 'textarea':
                    case 'text':
                    default:
                        $rule[] = 'string';
                        break;
                }

                $rules[$field->document_key] = $rule;
            }
        }

        return $rules;
    }

}
