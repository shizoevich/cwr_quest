<?php


namespace App\Http\Requests\Patient\DocumentRequest;


use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Illuminate\Foundation\Http\FormRequest;

class GetFilledDocumentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var PatientDocumentRequest $documentRequest */
        $documentRequest = $this->route()->parameter('document_request');
        if ($this->route()->hasParameter('document_request_item')) {
            $documentRequestItem = $this->route()->parameter('document_request_item');
            $hasItem = $documentRequest->id == $documentRequestItem->request_id;
        } else {
            $hasItem = true;
        }
        $filled = $documentRequest->items->filter(function ($item) {
            return $item->filled_at === null;
        })->isEmpty();
       
        return $hasItem && $filled;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}