<?php


namespace App\Http\Requests\Patient\DocumentRequest;


use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Illuminate\Foundation\Http\FormRequest;

class GetFilledDocumentRequest extends FormRequest
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
        $documentRequestItem = $this->route()->parameter('document_request_item');
       
        return $documentRequest->id == $documentRequestItem->request_id && $documentRequestItem->filled_at;
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